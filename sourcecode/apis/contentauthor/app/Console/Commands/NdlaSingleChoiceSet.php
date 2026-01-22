<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\AuditLog;
use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\H5PContent;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Helper\ProgressBar;

class NdlaSingleChoiceSet extends Command
{
    protected $signature = 'ndla:single-choice-set
                            {--r|resume= : Resume from ID, i.e. skip resources where ID is less than this}';
    protected $description = 'Disable behaviour setting "autoContinue" on existing H5P.SingleChoiceSet content';
    protected $help = 'The behaviour setting was added in H5PSingleChoiceSet in version 1.9.4, only content using v1.9.4 or newer will be updated.';

    private bool $cancelRequested = false;
    private ProgressBar $progress;
    private int $updatedCount = 0;
    private int $unchangedCount = 0;
    private int $failedCount = 0;
    private array $batchUpdated = [];
    private array $batchUnchanged = [];
    private array $batchFailed = [];
    private string $runId;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->progress = $this->output->createProgressBar();
        $this->progress->setBarCharacter('<info>-</info>');
        $this->progress->setEmptyBarCharacter(' ');
        $this->progress->setProgressCharacter('<comment>></comment>');
        $this->runId = Uuid::uuid4()->toString();

        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () {
            if ($this->progress->getMaxSteps() > 0) {
                $this->progress->setMessage('Cancelling...');
                $this->progress->setMessage("", 'cid');
                $this->progress->display();
                $this->cancelRequested = true;
            } else {
                // @phpstan-ignore-next-line
                exit(1);
            }
        });

        $resumeId = $this->option('resume') ?: 0;
        $libraries = collect(DB::select(<<<SQL
            SELECT id, major_version, minor_version, patch_version
            FROM h5p_libraries
            WHERE name = 'H5P.SingleChoiceSet'
            AND (
                major_version > 1
                OR (major_version = 1 AND minor_version > 9)
                OR (major_version = 1 AND minor_version = 9 and patch_version >=4)
            )
            ORDER BY id
        SQL));
        $this->info("Found <comment>{$libraries->count()}</comment> libraries named <comment>H5P.SingleChoiceSet</comment> that are <comment>version 1.9.4 or newer</comment>");

        if ($resumeId > 0) {
            $this->warn("Skipping content with ID less than <comment>{$resumeId}</comment>");
        }
        $content = DB::table('h5p_contents AS hc')
            ->select('hc.id')
            ->leftJoin('content_versions AS cv1', 'cv1.id', '=', 'hc.version_id')
            ->leftJoin('content_versions as cv2', 'cv2.parent_id', '=', 'cv1.id')
            ->whereIn('hc.library_id', $libraries->pluck('id'))
            ->where('hc.id', '>=', $resumeId)
            ->where(function ($query) {
                $query
                    ->whereNull('cv2.id')
                    ->orWhereNotIn('cv2.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
            })
        ;

        $contentCount = $content->count();
        $this->newLine();
        $this->info("Total number of content <comment>{$contentCount}</comment>");

        if ($this->confirm("Continue") !== true) {
            return;
        }
        $this->progress->setMaxSteps($contentCount);
        $this->progress->setMessage('', 'cid');
        $this->progress->setMessage('Processing Content id');
        $this->progress->setFormat("<info>%message%</info> <comment>%cid%</comment>\n%current%/%max% [%bar%] %percent:3s%%  Remaining time: %remaining:6s%");

        $content->chunkById(50, function ($contentsIds) {
            $this->batchUpdated = [];
            $this->batchUnchanged = [];
            $this->batchFailed = [];

            foreach ($contentsIds as $contentId) {
                $this->progress->setMessage((string)$contentId->id, 'cid');
                $this->progress->advance();
                $this->progress->display();

                $content = H5PContent::find($contentId->id);
                $params = json_decode($content->parameters);
                if (!isset($params->behaviour)) {
                    $params->behaviour = (object)[
                        'autoContinue' => false,
                    ];
                } else {
                    $params->behaviour->autoContinue = false;
                }

                $content->parameters = json_encode($params);
                if ($content->isDirty(['parameters'])) {
                    $content->filtered = '';
                        if ($content->save()) {
                            // Create new version record
                            event(new H5PWasSaved($content, new Request(), 'Disable autoContinue', $content));

                            $this->batchUpdated[] = $contentId->id;
                            $this->updatedCount++;
                        } else {
                            $this->batchFailed[] = $contentId->id;
                            $this->failedCount++;
                        }
                } else {
                    $this->batchUnchanged[] = $contentId->id;
                    $this->unchangedCount++;
                }

                if ($this->cancelRequested) {
                    AuditLog::log(
                        'Disable H5P.SingleChoiceSet autoContinue',
                        json_encode([
                            'runId' => $this->runId,
                            'library' => 'H5P.SingleChoiceSet',
                            'updated_ids' => $this->batchUpdated,
                            'unchanged_ids' => $this->batchUnchanged,
                            'failed_ids' => $this->batchFailed,
                            'status' => 'batch cancelled',
                        ])
                    );

                    $this->progress->setMessage("<error>Process was cancelled. You can use option '-r{$contentId->id}' to resume</error>");
                    $this->progress->display();
                    return false;
                }
            }

            AuditLog::log(
                'Disable H5P.SingleChoiceSet autoContinue',
                json_encode([
                    'runId' => $this->runId,
                    'library' => 'H5P.SingleChoiceSet',
                    'updated_ids' => $this->batchUpdated,
                    'unchanged_ids' => $this->batchUnchanged,
                    'failed_ids' => $this->batchFailed,
                    'status' => 'batch completed',
                ])
            );

            return true;
        }, 'hc.id', "id");

        if (!$this->cancelRequested) {
            $this->progress->setMessage('', 'cid');
            $this->progress->setMessage('Completed');
            $this->progress->finish();
        }
        $this->newLine(2);
        $this->info("Total content updated: <comment>{$this->updatedCount}</comment>");
        $this->info("Unchanged content: <comment>{$this->unchangedCount}</comment>");
        $this->info("Failed content: <comment>{$this->failedCount}</comment>");
        $this->newLine();
    }
}
