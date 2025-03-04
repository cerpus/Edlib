<?php

namespace App\Console\Commands;

use App\H5PContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class H5PContentCheck extends Command
{
    protected $signature = 'h5p:content-check
                            {--d|details : Show more details about the resources found}';

    protected $description = "Performs various checks on the content";

    public function handle()
    {
        $this->newLine();
        $this->missingContentType();

        $this->newLine();
        $this->invalidContentType();

        $this->newLine();
        $this->referencingMissingLibrary();

        $this->newLine();
        $this->withoutLicence();
    }

    private function missingContentType(): void
    {
        $this->output->write('<fg=cyan>Content where content type is missing or not set:</> ');

        $content = H5PContent::select(
            [
                'id',
                'title',
                'library_id',
                DB::raw("CASE WHEN is_published = 1 THEN 'Y' ELSE 'N' END AS published"),
            ],
        )
            ->doesntHave('library')
            ->get();

        if ($content->isEmpty()) {
            $this->info('None found');
        } else {
            $this->warn($content->count() . ' found');
        }

        if ($this->option('details')) {
            $this->table(['Id', 'Title', 'Library id', 'Is published'], $content);
        } else {
            $this->warn($content->implode('id', ', '));
        }
    }

    private function invalidContentType(): void
    {
        $this->output->write('<fg=cyan>Content with non-runnable content type:</> ');

        $content = H5PContent::select(
            [
                'h5p_contents.id',
                'h5p_contents.title',
                DB::raw("CASE WHEN is_published = 1 THEN 'Y' ELSE 'N' END AS published"),
                'library_id',
                'h5p_libraries.name',
            ],
        )
            ->join('h5p_libraries', 'h5p_libraries.id', '=', 'library_id')
            ->where('h5p_libraries.runnable', '=', 0)
            ->get();

        if ($content->isEmpty()) {
            $this->info('None found');
        } else {
            $this->warn($content->count() . ' found');
        }
        if ($this->option('details')) {
            $this->table(
                ['Id', 'Title', 'Is published', 'Library id', 'library name'],
                $content,
            );
        } else {
            $this->warn($content->implode('id', ', '));
        }
    }

    private function referencingMissingLibrary(): void
    {
        $this->output->write('<fg=cyan>Contents with reference to missing library:</> ');
        $content = H5PContent::select(
            [
                'h5p_contents.id',
                'h5p_contents.title',
                DB::raw("CASE WHEN is_published = 1 THEN 'Y' ELSE 'N' END AS published"),
                'h5p_contents_libraries.library_id',
            ],
        )
            ->join('h5p_contents_libraries', 'h5p_contents_libraries.content_id', '=', 'h5p_contents.id')
            ->leftJoin('h5p_libraries', 'h5p_libraries.id', '=', 'h5p_contents_libraries.library_id')
            ->whereNull('h5p_libraries.id')
            ->get();

        if ($content->isEmpty()) {
            $this->info('None found');
        } else {
            $this->warn($content->count() . ' found');
        }
        if ($this->option('details')) {
            $this->table(
                ['Id', 'Title', 'Is published', 'Library id'],
                $content,
            );
        } else {
            $this->warn($content->implode('id', ', '));
        }
    }

    private function withoutLicence(): void
    {
        $this->output->write('<fg=cyan>Contents without license:</> ');
        $content = H5PContent::select(
            [
                'id',
                'title',
                DB::raw("CASE WHEN is_published = 1 THEN 'Y' ELSE 'N' END AS published"),
            ],
        )
            ->whereNull('license')
            ->orWhere('license', '=', '')
            ->get();

        if ($content->isEmpty()) {
            $this->info('None found');
        } else {
            $this->warn($content->count() . ' found');
        }
        if ($this->option('details')) {
            $this->table(
                ['Id', 'Title', 'Is published'],
                $content,
            );
        } else {
            $this->warn($content->implode('id', ', '));
        }
    }
}
