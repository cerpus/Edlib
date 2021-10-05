<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\CourseExportJob;
use App\NdlaArticleImportStatus;
use App\Jobs\ImportTopicArticles;
use App\Http\Controllers\Controller;
use App\Libraries\NDLA\API\GraphQLApi;
use Predis\Connection\ConnectionException;
use App\Traits\HandlesHugeSubjectMemoryLimit;
use App\Libraries\NDLA\Exporters\EdStepExport;
use App\Libraries\NDLA\Traits\ImportOwnerTrait;

class NDLACourseImportController extends Controller
{
    use ImportOwnerTrait, HandlesHugeSubjectMemoryLimit;

    /** @var GraphQLApi */
    protected $gqlApi;

    /** @var EdStepExport */
    protected $courseExporter;

    public function __construct()
    {
        $this->gqlApi = resolve(GraphQLApi::class);
        $this->courseExporter = resolve(EdStepExport::class);
    }

    public function index(Request $request)
    {

        $subjects = $this->gqlApi->fetchSubjects();

        uasort($subjects, function ($a, $b) {
            if ($a->name == $b->name) {
                return 0;
            }
            return ($a->name < $b->name) ? -1 : 1;
        });

        return view('admin.ndla-course-import.index')->with(compact('subjects'));
    }

    public function subjectPreview(Request $request, $subjectId)
    {
        $this->expandMemoryLimitForHugeSubjects($subjectId);

        $subject = $this->courseExporter->processSubject($subjectId);
        $owner = $this->getImportOwner();

        return view('admin.ndla-course-import.subject-preview')->with(compact('subject', 'subjectId', 'owner'));
    }

    public function export(Request $request, $subjectId, $topicId)
    {
        $this->expandMemoryLimitForHugeSubjects($subjectId);

        $job = CourseExportJob::dispatch($subjectId, $topicId)->onQueue('ndla');

        $request->session()->flash('message',
            "Export of the course has started. Please be patient as this can take some time. An Edit button will appear in the course preview when the export is done.");

        return back();
    }

    public function articleImport(Request $request, $subjectId, $topicId)
    {
        $importId = Str::random(10);
        $message = "Import ID: $importId<br>Started import of all unimported articles in course.<br>This can take some time.";
        try {
            $job = ImportTopicArticles::dispatch($subjectId, $topicId, $importId)->onQueue('ndla');
            if (!$job) {
                throw new \Exception("Job was not created.", 11);
            }
        } catch (\Throwable $t) {
            $message = "Import ID: $importId<br>There was an error adding the import job to the queue. ({$t->getCode()}) {$t->getMessage()}";
            NdlaArticleImportStatus::addStatus(0, $message, $importId);
        }

        $request->session()->flash('message', $message);

        return back();
    }
}
