<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\InvalidIconException;
use App\Models\ContentVersion;
use App\Utils\IconDownloader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Log\LoggerInterface;
use Throwable;

final class DownloadIconForContent implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public int $tries = 3;

    public function __construct(private ContentVersion $contentVersion) {}

    /**
     * @throws Throwable
     */
    public function handle(
        IconDownloader $downloader,
        LoggerInterface $logger,
    ): void {
        $url = $this->contentVersion->original_icon_url;

        if ($url === null) {
            $logger->info('No icon to download for content version {version}', [
                'version' => $this->contentVersion->id,
            ]);

            return;
        }

        try {
            $upload = $downloader->downloadAndStore($url);

            $this->contentVersion->icon()->associate($upload);
            $this->contentVersion->save();
        } catch (InvalidIconException $e) {
            $logger->info('The downloaded icon was invalid: {message}', [
                'content_version' => $this->contentVersion->id,
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
        }
    }
}
