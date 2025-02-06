<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Content;
use App\Models\Context;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachContextToContents implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Context $context) {}

    public function handle(): void
    {
        Content::lazy()->each(function (Content $content) {
            $content->contexts()->syncWithoutDetaching([$this->context]);
        });
    }
}
