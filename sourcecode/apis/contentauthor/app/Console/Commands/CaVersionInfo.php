<?php

namespace App\Console\Commands;

use App\ContentVersion;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CaVersionInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ca:version-info {id* : Version id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Info about content version';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>Leaf nodes are content that do not have any children with version purpose <fg=yellow>"Update"</> or <fg=yellow>"Upgrade"</></>');

        foreach ($this->argument('id') as $contentId) {
            $this->newLine();
            $this->versionInfo($contentId);
        }

        return SymfonyCommand::SUCCESS;
    }

    private function versionInfo(string $id): void
    {
        $version = ContentVersion::find($id);
        if ($version === null) {
            $this->error("'$id' was not found");
            return;
        }
        $isLeaf = $version->isLeaf();

        $output = [
            ['Id', $version->id],
            ['Created', $version->created_at->format('Y-m-d H:i:s e')],
            ['Version purpose', $version->version_purpose],
            ['Content id', $version->content_id],
            ['Content type', $version->content_type],
            ['User id', $version->user_id],
            ['Linear versioning', $version->linear_versioning ? 'Yes' : 'No'],
            ['Leaf node', $isLeaf ? 'Yes' : 'No'],
            ['Leaf node id', $isLeaf ? $version->id : $version->latestLeafVersion()?->id],
            ['Parent', $version->previousVersion?->id],
            ['Children', $version->nextVersions->implode('id', "\n")],
        ];

        $this->info("Details for version $id");
        $this->table([], $output, 'symfony-style-guide');
    }
}
