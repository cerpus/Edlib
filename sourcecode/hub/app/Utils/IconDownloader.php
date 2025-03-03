<?php

declare(strict_types=1);

namespace App\Utils;

use App\Exceptions\InvalidIconException;
use App\Models\Upload;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

use function assert;
use function hash_file;
use function is_string;
use function sprintf;

final readonly class IconDownloader
{
    /**
     * @var array<string, string>
     */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
    ];

    private const MAX_SIZE = 200000;

    public function __construct(
        private ClientInterface $client,
        private Cloud $disk,
    ) {}

    /**
     * Downloads the icon at the given URL, then uploads it to file storage
     * and returns the corresponding model.
     * @throws InvalidIconException if the icon is invalid
     * @throws Throwable
     */
    public function downloadAndStore(string $url): Upload
    {
        $tempPath = $this->downloadImage($url);
        $hash = hash_file('sha256', $tempPath);

        try {
            if ($hash === false) {
                throw new RuntimeException('Could not generate SHA256 hash for file');
            }

            return Upload::where('hash_sha256', $hash)->firstOr(function () use ($hash, $tempPath) {
                $mimeType = (new MimeTypes())->guessMimeType($tempPath);
                assert(is_string($mimeType));

                $extension = self::ALLOWED_TYPES[$mimeType] ??
                    throw new InvalidIconException("Invalid image type ($mimeType)");

                $path = sprintf('%s.%s', Str::ulid(), $extension);
                $this->disk->put($path, Utils::tryFopen($tempPath, 'r'), 'public');

                try {
                    $upload = new Upload();
                    $upload->path = $path;
                    $upload->mime_type = $mimeType;
                    $upload->hash_sha256 = $hash;
                    $upload->save();

                    return $upload;
                } catch (Throwable $e) {
                    $this->disk->delete($path);

                    throw $e;
                }
            });
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * @return string the temporary path to the downloaded file
     * @throws InvalidIconException
     * @throws GuzzleException
     */
    private function downloadImage(string $url): string
    {
        $tempName = tempnam(sys_get_temp_dir(), 'edlib_');
        if ($tempName === false) {
            throw new Exception('Could not create temporary file');
        }

        $this->client->request('GET', $url, [
            'headers' => [
                'Accept' => implode(',', array_keys(self::ALLOWED_TYPES)),
            ],
            'on_headers' => function (ResponseInterface $response) {
                $mimeType = explode(';', $response->getHeaderLine('Content-Type'))[0];

                if (!isset(self::ALLOWED_TYPES[$mimeType])) {
                    throw new InvalidIconException("Invalid file type ($mimeType)");
                }
            },
            'progress' => function (int $downloadTotal, int $downloadedBytes) {
                if ($downloadTotal > self::MAX_SIZE || $downloadedBytes > self::MAX_SIZE) {
                    throw new InvalidIconException('Icon exceeds max permitted size');
                }
            },
            'sink' => $tempName,
            'timeout' => 5,
        ]);

        return $tempName;
    }
}
