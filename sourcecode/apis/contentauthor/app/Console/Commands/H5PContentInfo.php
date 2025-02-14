<?php

namespace App\Console\Commands;

use App\ContentVersion;
use App\H5PContent;
use App\H5PContentLibrary;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PContentInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:content-info {id* : Content id}
                            {--d|dump      : Dump raw data as JSON}
                            {--c|content   : Basic info and the H5P content}
                            {--l|libraries : List libraries used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Info about H5P content';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!$this->option('content') && !$this->option('dump')) {
            $this->line('<fg=cyan>Leaf nodes are content that do not have any children with version purpose <fg=yellow>"Update"</> or <fg=yellow>"Upgrade"</></>');
        }

        foreach ($this->argument('id') as $contentId) {
            $this->newLine();

            if (!ctype_digit($contentId)) {
                $this->error("'$contentId' is not a valid H5P content id. Id must be an integer");
                $this->newLine();

                return SymfonyCommand::INVALID;
            }

            $content = H5PContent::find($contentId);
            if ($content === null) {
                $this->error("No information found for content '$contentId'");
                return SymfonyCommand::FAILURE;
            }

            if ($this->option('dump')) {
                $this->info('Row dump for content ' . $content->id);
                $this->line(json_encode($content->attributesToArray()));
            } elseif ($this->option('content')) {
                $this->contentOutput($content);
            } else {
                $this->contentInfo($content);
            }

            if ($this->option('libraries')) {
                $this->libraryInfo($content);
            }
        }

        return SymfonyCommand::SUCCESS;
    }

    private function contentInfo(H5PContent $content): void
    {
        $version = $content->getVersion();
        $parentId = $this->parentContentId($content->id);
        $children = $version?->nextVersions();

        $output = [
            ['Id', $content->id],
            ['Title', $content->title],
            ['Created', $content->created_at->format('Y-m-d H:i:s e')],
            ['Updated', $content->updated_at->format('Y-m-d H:i:s e')],
            ['Library Id', $content->library_id],
            ['Library', $content->library ? $content->library->getLibraryString(true) : '<fg=red>-- Not found --</>'],
            ['Language', $this->langName($content->language_iso_639_3)],
            ['License', $content->license],
            ['Published', $content->is_published ? 'Yes' : 'No'],
            ['Max score', $this->maxScore($content->max_score)],
            ['Bulk calculated', $this->bulkCalculation($content->bulk_calculated)],
            ['Owner Id', $content->user_id],
            ['Version Id', $content->version_id],
            ['Version purpose', $version?->version_purpose],
            ['All versions (newest first)', ContentVersion::select('id')->where('content_id', $content->id)->orderBy('created_at', 'desc')->implode('id', "\n")],
            ['Leaf node', $version?->isLeaf() ? 'Yes' : 'No'],
            ['Parent content id', $parentId],
            ['Children content ids', $children?->implode('content_id', ', ')],
        ];

        if (config('feature.allow-mode-switch')) {
            $output[] = ['Create mode', $content->content_create_mode];
        }

        $this->info('Details for content ' . $content->id);
        $this->table([], $output, 'symfony-style-guide');
    }

    private function libraryInfo(H5PContent $content): void
    {
        $libraries = H5PContentLibrary::where('content_id', $content->id)
            ->orderBy('dependency_type')
            ->orderBy('weight')
            ->get()
            ->map(function(H5PContentLibrary $cLib) {
                $library = $cLib->library;
                return [
                    'id' => $cLib->library_id,
                    'title' => $library ? $library->getLibraryString(true) : '<fg=red>-- Not found --</>',
                    'lib_type' => $library ? ($library->runnable ? 'Content type' : 'Library') : '',
                    'dep_type' => $cLib->dependency_type,
                ];
            })
        ;

        $this->newLine();
        $this->info('Libraries for content ' . $content->id);
        $this->table(['id', 'Title', 'Library type', 'Depencdency type'], $libraries);
    }

    private function contentOutput(H5PContent $content): void
    {
        $this->info(sprintf(
            "H5P content for <fg=cyan>%s - %s (%s)</>",
            $content->id,
            $content->title,
            $content->library ? $content->library->getLibraryString(true) : '<fg=red>-- Not found --</>'
        ));
        $this->line($content->parameters);
    }

    private function parentContentId(string $id): string|null
    {
        $contentVersion = ContentVersion::where('content_id', $id)->orderBy('created_at')->limit(1)->first();
        if (!$contentVersion) {
            $this->error("No versions found for content '$id'");
            return null;
        }
        return $contentVersion->previousVersion()->first()?->content_id;
    }

    private function maxScore(int|null $score): string
    {
        return match ($score) {
            0 => '0 => Score value, not supported or could not be calculated',
            null => 'null => Not calculated or no value returned',
            default => $score . ' => Score value',
        };
    }

    private function bulkCalculation(int $status): string
    {
        return match ($status) {
            0 => '0 => Not bulk calculated',
            1 => '1 => Bulk calculated',
            2 => '2 => Bulk calculation error',
            default => $status . ' => Unknown',
        };
    }

    private function langName(string|null $langCode): string
    {
        if (empty($langCode)) {
            return '';
        }

        $name = locale_get_display_language($langCode);

        return $langCode . ' => ' . (!$name || $name === $langCode ? '(Unknown)' : $name);
    }
}
