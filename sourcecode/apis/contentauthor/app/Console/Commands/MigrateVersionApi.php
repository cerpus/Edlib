<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Content;
use App\ContentVersion;
use App\Libraries\Versioning\FixedVersionClient;
use Carbon\Carbon;
use Cerpus\VersionClient\VersionData;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateVersionApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edlib:migrate-version-api {--debug}';

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

        if ($this->option('debug')) {
            $this->warn("Debug enabled");
        }

        $versionClient = new FixedVersionClient(config('versionClient.versionserver'));

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
                            try {
                                $data = $versionClient->getVersion($row->version_id);
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
                            } catch (Exception $e) {
                                if ($progress !== null) {
                                    $this->line('');
                                }

                                if ($e->getCode() !== 0) {
                                    $this->warn(sprintf(
                                        'Error "%s" from Version API for version id "%s" and content id "%s": %s',
                                        $e->getCode(),
                                        $row->version_id,
                                        $row->item_id,
                                        $e->getMessage()
                                    ));
                                    Log::warning('Version API migration: Error from Version API', [
                                        'content_type' => $contentType,
                                        'row' => $row,
                                        'code' => $e->getCode(),
                                        'message' => $e->getMessage(),
                                    ]);
                                } else {
                                    $this->warn(sprintf('Unknown error from Version API for version id "%s" and content id "%s"', $row->version_id, $row->item_id));
                                    Log::warning('Version API migration: Unknown error from Version API', [
                                        'content_type' => $contentType,
                                        'row' => $row,
                                    ]);
                                }
                            }
                            $progress?->advance();
                        }
                    }, $table . '.id', 'item_id');

                if ($progress !== null) {
                    $progress->finish();
                    $this->line('');
                }

                $this->info(sprintf('Versions for %s committed', $table));
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
                Log::error(sprintf('Exception/Error while migrating Version API for %s', $table), [$e]);

                throw $e;
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
