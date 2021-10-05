<?php

namespace App\Jobs;

use DB;
use App;
use App\CourseExport;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\NdlaArticleImportStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Queue\SerializesModels;
use App\Libraries\NDLA\API\GraphQLApi;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Traits\HandlesHugeSubjectMemoryLimit;
use App\Libraries\NDLA\Importers\APIArticleImporter;

class ImportTopicArticles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesHugeSubjectMemoryLimit;

    public $subjectId, $topicId;

    public $importId;

    public $timeout = 3600; // 60 minutes timeout for this bulk import job.

    /** @var App\Libraries\NDLA\API\GraphQLApi */
    public $gqlApi;

    /** @var CourseExport */
    public $courseLog;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subjectId, $topicId, $importId = null)
    {
        $this->subjectId = $subjectId;
        $this->topicId = $topicId;

        if (!$this->importId = $importId) {
            $this->importId = Str::random(10);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0);
        $this->expandMemoryLimitForHugeSubjects($this->subjectId);

        $this->courseLog = CourseExport::byNdlaId($this->topicId);
        NdlaArticleImportStatus::addStatus(0, "Verifying DB connection.", $this->importId);
        $this->verifyDbConnection();
        NdlaArticleImportStatus::addStatus(0, "DB connection verified OK.", $this->importId);

        try {
            $this->gqlApi = resolve(GraphQLApi::class);

            $subject = $this->gqlApi->fetchSubjectMinimal($this->subjectId);

            $topicFound = null;
            $topicId = strtolower($this->topicId);
            foreach ($subject->topics as $key => $topic) {
                if (strtolower($topic->id) === $topicId) {
                    $subject->topics = [$topic];
                    break;
                }
            }

            $articlesToImport = [];
            $titlePrefixes = [];

            foreach ($subject->topics ?? [] as $topic) {
                foreach ($topic->subtopics as $subtopic) {
                    foreach ($subtopic->coreResources ?? [] as $resource) {
                        if ($resource->contentUri ?? null) {
                            $articlesToImport[] = last(explode(':', $resource->contentUri));
                        }
                    }
                    foreach ($subtopic->supplementaryResources ?? [] as $resource) {
                        if ($resource->contentUri ?? null) {
                            $articlesToImport[] = last(explode(':', $resource->contentUri));
                        }
                    }

                    //Reach down one level and pull the content up
                    foreach ($subtopic->subtopics as $subSubtopic) {
                        foreach ($subSubtopic->coreResources ?? [] as $resource) {
                            if ($resource->contentUri ?? null) {
                                $articlesToImport[] = last(explode(':', $resource->contentUri));
                                $titlePrefixes[last(explode(':', $resource->contentUri))] = $subSubtopic->name ?? 'Subtopic name';
                            }
                        }
                        foreach ($subSubtopic->supplementaryResources ?? [] as $resource) {
                            if ($resource->contentUri ?? null) {
                                $articlesToImport[] = last(explode(':', $resource->contentUri));
                                $titlePrefixes[last(explode(':', $resource->contentUri))] = $subSubtopic->name ?? 'Subtopic name';
                            }
                        }
                    }

                }

                foreach ($topic->coreResources ?? [] as $resource) {
                    if ($resource->contentUri ?? null) {
                        $articlesToImport[] = last(explode(':', $resource->contentUri));
                    }
                }

                foreach ($topic->supplementaryResources ?? [] as $resource) {
                    if ($resource->contentUri ?? null) {
                        $articlesToImport[] = last(explode(':', $resource->contentUri));
                    }
                }
            }

            /** @var APIArticleImporter $articleImporter */
            $articleImporter = resolve(APIArticleImporter::class);

            $unimportedArticles = [];

            foreach ($articlesToImport as $articleId) {
                if (!App\NdlaIdMapper::articleByNdlaId($articleId)) { // Not imported
                    if (App\NdlaArticleId::find($articleId)) { // But available for import
                        $unimportedArticles[] = $articleId;
                    }
                }
            }

            $unimportedArticles = array_unique($unimportedArticles);

            $articlesToImportCount = count($unimportedArticles);

            NdlaArticleImportStatus::addStatus(0, "Starting import of $articlesToImportCount articles.", $this->importId);

            $articleImporter->setImportId($this->importId);
            $importedArticleCount = $articleImporter->importArticles($unimportedArticles, $titlePrefixes);

            NdlaArticleImportStatus::addStatus(0, "Imported $importedArticleCount articles.", $this->importId);

            $this->courseLog->message = "[{$this->importId}] Imported $importedArticleCount articles to {$subject->name}";
            $this->courseLog->save();
        } catch (\Throwable $t) {
            NdlaArticleImportStatus::logError(0, " Failed to import {$this->subjectId}. Message: {$t->getMessage()}", $this->importId);

            $this->courseLog->message = "[{$this->importId}] Failed to import {$this->subjectId}. Message: {$t->getMessage()}";
            $this->courseLog->save();

            throw $t;
        }
    }

    public function verifyDbConnection(): void
    {
        try {
            $connection = Schema::hasTable('ndla_id_mappers');
        } catch (\PDOException $e) {
            $connection = DB::reconnect();
            if (!$connection) {
                $message = "Unable to reconnect to DB: ({$e->getCode()}) {$e->getMessage()}";
                $this->courseLog->message = $message;
                $this->courseLog->save();
                NdlaArticleImportStatus::logError(0, $message, $this->importId);
                NdlaArticleImportStatus::logError(0, $e->getTraceAsString(), $this->importId);

                throw $e;
            }

            NdlaArticleImportStatus::logDebug(0, "Reconnected to DB.", $this->importId);
        }
    }
}
