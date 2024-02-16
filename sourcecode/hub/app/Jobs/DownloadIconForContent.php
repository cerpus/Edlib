<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\InvalidIconException;
use App\Models\ContentVersion;
use App\Models\Upload;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

use function array_keys;
use function assert;
use function explode;
use function hash_file;
use function implode;
use function is_string;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

final class DownloadIconForContent implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const MAX_SIZE = 200000;

    /** @var array<string, string> */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
    ];

    public bool $deleteWhenMissingModels = true;

    public int $tries = 3;

    public function __construct(
        private ContentVersion $contentVersion,
        private readonly string $url,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(
        ClientInterface $client,
        Cloud $fs,
        LoggerInterface $logger,
    ): void {
        $tempPath = $this->downloadImage($client);

        try {
            $hash = hash_file('sha256', $tempPath);
            if ($hash === false) {
                throw new Exception('Could not generate SHA256 hash for file');
            }

            DB::beginTransaction();
            $uploadedName = null;
            try {
                $upload = Upload::where('hash_sha256', $hash)->firstOr(function () use ($fs, $hash, $tempPath, &$uploadedName) {
                    $mimeType = (new MimeTypes())->guessMimeType($tempPath);
                    assert(is_string($mimeType));

                    $extension = self::ALLOWED_TYPES[$mimeType] ??
                        throw new RuntimeException("Invalid image type ($mimeType)");

                    $name = sprintf('%s.%s', Str::ulid(), $extension);
                    $fs->put($name, Utils::tryFopen($tempPath, 'r'), 'public');

                    $uploadedName = $name;

                    $upload = new Upload();
                    $upload->path = $uploadedName;
                    $upload->mime_type = $mimeType;
                    $upload->hash_sha256 = $hash;
                    $upload->save();
                });

                $this->contentVersion->icon()->associate($upload);
                $this->contentVersion->save();

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();

                if ($uploadedName) {
                    $fs->delete($uploadedName);
                }

                throw $e;
            }
        } catch (ClientException $e) {
            $logger->info('Got a {code} while downloading content icon', [
                'code' => $e->getCode(),
                'content_version' => $this->contentVersion->id,
                'exception' => (string) $e,
                'url' => $this->url,
            ]);
        } catch (InvalidIconException $e) {
            $logger->info('The downloaded icon was invalid: {message}', [
                'content_version' => $this->contentVersion->id,
                'message' => $e->getMessage(),
                'url' => $this->url,
            ]);
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * @return string the temporary path to the downloaded file
     * @throws GuzzleException
     */
    private function downloadImage(ClientInterface $client): string
    {
        $tempName = tempnam(sys_get_temp_dir(), 'edlib_');
        if ($tempName === false) {
            throw new Exception('Could not create temporary file');
        }

        $client->request('GET', $this->url, [
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
        ]);

        return $tempName;
    }
}
