<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Content;
use App\ContentVersion;
use Carbon\Carbon;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MigrateVersionApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edlib:migrate-version-api {--dry-run} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migrate version data from Version API for Articles, Games, H5Ps and Links to local table";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function debug($message)
    {
        if ($this->option("debug")) {
            $this->warn($message);
        }
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);

        $this->comment("Migrate version data from Version API for Articles, Games, H5Ps and Links to local table");

        if ($this->option('dry-run')) {
            $this->warn("Dry-run mode enabled");
        }

        if ($this->option('debug')) {
            $this->warn("Debug enabled");
        }

        $versionClient = app(VersionClient::class);

        $tables = [
            'articles' => Content::TYPE_ARTICLE,
            'games' => Content::TYPE_GAME,
            'links' => Content::TYPE_LINK,
            'h5p_contents' => Content::TYPE_H5P,
        ];

        foreach ($tables as $table => $contentType) {
            try {
                $count = DB::table($table)
                    ->leftJoin('content_versions', $table.'.version_id', '=', 'content_versions.id')
                    ->whereNull('content_versions.id')
                    ->whereNotNull($table . '.version_id')
                    ->count();

                if ($count === 0) {
                    $this->info('No records to process for ' . $table);
                    continue;
                }

                DB::transaction(function () use ($table, $versionClient, $contentType, $count) {
                    if ($this->option('debug')) {
                        $progress = null;
                        $this->info('Migrating data for ' . $table);
                    } else {
                        $progress = $this->output->createProgressBar($count);
                        $progress->setFormat('%message%: %current%/%max% [%bar%] %percent:3s%% %estimated:6s%');
                        $progress->setBarCharacter('-');
                        $progress->setEmptyBarCharacter(' ');
                        $progress->setProgressCharacter('>');
                        $progress->setMessage('Migrating data for ' . $table);
                    }

                    DB::table($table)
                        ->select([$table . '.id as item_id', $table . '.version_id'])
                        ->leftJoin('content_versions', $table.'.version_id', '=', 'content_versions.id')
                        ->whereNull('content_versions.id')
                        ->whereNotNull($table . '.version_id')
                        ->orderBy($table . '.id')
                        ->chunkById(100, function (Collection $rows) use ($versionClient, $progress, $contentType) {
                            $this->debug(sprintf('Chunk with %d row(s)', $rows->count()));
                            foreach ($rows as $row) {
                                $data = $versionClient->getVersion($row->version_id);
                                if ($data) {
                                    $this->debug(sprintf('Creating version "%s" for content id "%s"', $data->getId(), $data->getExternalReference()));
                                    $parent = $data->getParent();
                                    $this->addMissingParentRecord($parent, $contentType);
                                    if (ContentVersion::where('id', $data->getId())->doesntExist()) {
                                        DB::insert('insert into content_versions (id, content_id, content_type, parent_id, created_at, version_purpose, user_id, linear_versioning) values (?,?,?,?,?,?,?,?)', [
                                            $data->getId(),
                                            $data->getExternalReference(),
                                            $contentType,
                                            $parent?->getId(),
                                            Carbon::createFromTimestampMs($data->getCreatedAt())->format('Y-m-d H:i:s.u'),
                                            $data->getVersionPurpose(),
                                            $data->getUserId(),
                                            $data->isLinearVersioning(),
                                        ]);
                                    } else {
                                        if ($progress !== null) {
                                            $this->line('');
                                        }
                                        $this->warn(sprintf('Version "%s" already exists', $data->getId()));
                                    }
                                } else {
                                    if ($progress !== null) {
                                        $this->line('');
                                    }
                                    if ($versionClient->getErrorCode() !== null) {
                                        $message = $versionClient->getError();
                                        if (is_array($message)) {
                                            $message = implode(' # ', $message);
                                        }
                                        $this->warn(sprintf('Error "%s" from Version API for version id "%s" and content id "%s": %s',
                                            $versionClient->getErrorCode(),
                                            $row->version_id,
                                            $row->item_id,
                                            $message
                                        ));
                                        Log::warning('Version API migration: Error from Version API', [$contentType, $row, $versionClient->getErrorCode(), $versionClient->getError()]);
                                    } else {
                                        $this->warn(sprintf('Unknown error from Version API for version id "%s" and content id "%s"', $row->version_id, $row->item_id));
                                        Log::warning('Version API migration: Unknown error from Version API', [$contentType, $row]);
                                    }
                                    // Recreate client to reset error code and message
                                    $versionClient = app(VersionClient::class);
                                }
                                $progress?->advance();
                            }
                        }, $table . '.id', 'item_id');

                    if ($progress !== null) {
                        $progress->finish();
                        $this->line('');
                    }

                    if ($this->option('dry-run')) {
                        throw new RuntimeException("Dry-run enabled, rolling back changes");
                    } else {
                        $this->debug('Committing changes...');
                    }
                });
                $this->info(sprintf('Versions for %s committed', $table));
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
                $this->error('Changes was not committed');
                Log::error(sprintf('Exception/Error while migrating Version API for %s, changes was not committed', $table), [$e]);
            }
        }

        return 0;
    }

    /**
     * A version has a parent version that does not exist, create a version record for the missing version
     */
    private function addMissingParentRecord(?VersionData $data, string $contentType): void
    {
        if ($data !== null && ContentVersion::where('id', $data->getId())->doesntExist()) {
            $this->debug(sprintf('Creating missing parent version "%s" for content id "%s"', $data->getId(), $data->getExternalReference()));
            $parent = $data->getParent();
            $this->addMissingParentRecord($parent, $contentType);
            DB::insert('insert into content_versions (id, content_id, content_type, parent_id, created_at, version_purpose, user_id, linear_versioning) values (?,?,?,?,?,?,?,?)', [
                $data->getId(),
                $data->getExternalReference(),
                $contentType,
                $parent?->getId(),
                Carbon::createFromTimestampMs($data->getCreatedAt())->format('Y-m-d H:i:s.u'),
                $data->getVersionPurpose(),
                $data->getUserId(),
                $data->isLinearVersioning(),
            ]);
        }
    }
}
