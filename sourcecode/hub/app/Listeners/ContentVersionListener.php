<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ContentVersionDeleting;
use App\Events\ContentVersionSaving;
use App\Exceptions\InvalidIconException;
use App\Jobs\DownloadIconForContent;
use App\Utils\IconDownloader;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ContentVersionListener
{
    public function __construct(
        private IconDownloader $downloader,
        private Dispatcher $dispatcher,
        private LoggerInterface $logger,
    ) {}

    public function handleDeletion(ContentVersionDeleting $event): void
    {
        $event->version->tags()->detach();
    }

    public function handleIcon(ContentVersionSaving $event): void
    {
        if ($event->version->original_icon_url === null) {
            return;
        }

        try {
            // Attempt downloading synchronously *once* to give the LTI platform
            // a chance at having the icon.
            $upload = $this->downloader->downloadAndStore(
                $event->version->original_icon_url,
            );

            $event->version->icon()->associate($upload);
        } catch (InvalidIconException $e) {
            // The downloaded image was not to our liking. Don't retry.
            $this->logger->info('The downloaded icon was invalid: {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        } catch (Throwable $e) {
            $this->logger->warning('Failed to download the icon at {url} synchronously: {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
                'url' => $event->version->original_icon_url,
            ]);

            // Attempt again, but on an asynchronous job.
            $this->dispatcher->dispatch(
                new DownloadIconForContent($event->version),
            );
        }
    }
}
