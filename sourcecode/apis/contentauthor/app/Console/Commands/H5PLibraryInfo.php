<?php

namespace App\Console\Commands;

use App\H5PContent;
use App\H5PLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PLibraryInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-info {library* : Id or machine name of library}
                            {--c|contents : List leaf contents for library}
                            {--a|all      : List all contents for library}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Info for installed H5P libraries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>Counted/Listed may include content that have been deleted or upgraded to a newer library version</>');

        $libraryIds = [];
        foreach ($this->argument('library') as $arg) {
            if (ctype_digit($arg)) {
                $libraryIds[] = intval($arg);
            } else {
                $ids = $this->idsByMachineName($arg);
                if (count($ids) === 0) {
                    $this->error('Machine name ' . $arg . ' was not found');
                    continue;
                }
                $libraryIds = array_merge($libraryIds, $ids);
            }
        }

        foreach ($libraryIds as $libraryId) {
            $library = H5PLibrary::find($libraryId);
            if ($library) {
                $this->libraryInfo($library);
            } else {
                $this->error('Library with id ' . $libraryId . ' was not found');
            }
            if ($this->option('contents') || $this->option('all')) {
                $this->listContents($libraryId);
            }
        }

        return SymfonyCommand::SUCCESS;
    }

    private function libraryInfo(H5PLibrary $library): void
    {
        $output = [
            ['Id', $library->id],
            ['Machine name', $library->name],
            ['Title', $library->title],
            ['Major version', $library->major_version],
            ['Minor version', $library->minor_version],
            ['Patch version', $library->patch_version],
            ['Installed', $library->created_at?->format('Y-m-d H:i:s e')],
            ['Updated', $library->updated_at?->format('Y-m-d H:i:s e')],
            ['Runnable / Content type', $library->runnable ? 'Yes' : 'No'],
            ['Patch version in folder name', $library->patch_version_in_folder_name ? 'Yes' : 'No'],
            ['Contents', $library->contents()->count()],
            ["Translations ({$library->languages()->count()})", $library->languages()->implode('language_code', "\n")],
        ];

        $this->newLine();
        $this->info("Details for library $library->id - ". $library->getLibraryString(true));
        $this->table([], $output, 'symfony-style-guide');
    }

    private function listContents(int $libraryId): void
    {
        $total = 0;

        $content = H5PContent::where('library_id', '=', $libraryId)
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->get()
            ->map(function ($item) use (&$total) {
                $total++;
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'created' => $item->created_at->format('Y-m-d H:i:s e'),
                    'updated' => $item->updated_at->format('Y-m-d H:i:s e'),
                    'published' => $item->is_published ? 'Yes' : 'No',
                    'leaf' => $item->getVersion()?->isLeaf() ? 'Yes' : 'No',
                ];
            })
            ->when(!$this->option('all'),
                fn(Collection|null $items) => $items !== null ? $items->filter(fn(array $item) => $item['leaf'] === 'Yes') : false,
            );

        if (count($content) > 0) {
            $this->newLine();
            $this->info('For more info about a content use \'<fg=yellow>php artisan h5p:content-info <id>...</>\'');
            $this->line('<fg=cyan>Leaf nodes are content that do not have any children with version purpose <fg=yellow>"Update"</> or <fg=yellow>"Upgrade"</></>');
            $this->newLine();
            if ($this->option('all')) {
                $this->info("<fg=yellow>{$content->count()}</> content");
            } else {
                $this->info("<fg=yellow>{$content->count()}</> leaf content, <fg=yellow>$total</> total (Use option <fg=yellow>--a</> to list all)");
            }

            $this->table(
                ['Id', 'Title', 'Created', 'Updated', 'Published', 'Leaf node'],
                $content
            );
        } else {
            $this->warn("No content found");
        }
    }

    private function idsByMachineName(string $machineName): array
    {
        return H5PLibrary::select('id')
            ->where('name', '=', $machineName)
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('id')
            ->all();
    }
}
