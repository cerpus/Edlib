<?php

namespace App\Http\Controllers\Admin;

use App\LearningPath;
use App\LearningPathStep;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\NDLA\API\LearningPathApiClient;

class NDLALearningPathImportController extends Controller
{
    public function index(Request $request)
    {
        $learningPaths = LearningPath::paginate();

        /** @var LearningPathApiClient $learningPathApiClient */
        $learningPathApiClient = resolve(LearningPathApiClient::class);
        $learningPathsApiCount = $learningPathApiClient->fetchTotalCount();

        return view('admin.learningpath-import.index')->with(compact('learningPaths', 'learningPathsApiCount'));
    }

    public function sync(Request $request)
    {
        /** @var LearningPathApiClient $learningPathApiClient */
        $learningPathApiClient = resolve(LearningPathApiClient::class);
        $maxPaths = $learningPathApiClient->fetchTotalCount();
        $maxPages = $learningPathApiClient->getMaxPages();

        for ($page = 1; $page <= $maxPages; $page++) {
            $learningPaths = $learningPathApiClient->fetchLearningPaths($page);
            foreach ($learningPaths as $learningPath) {
                try {
                    $fullLearningPath = $learningPathApiClient->fetchLearningPath($learningPath->id);
                    $learningPathSteps = $fullLearningPath->learningsteps;

                    $learningPath = LearningPath::updateOrCreate(
                        [
                            'id' => $learningPath->id
                        ],
                        [
                            'title' => $learningPath->title->title,
                            'json' => $learningPath
                        ]
                    );
                    $learningPath->steps()->delete();

                    foreach ($learningPathSteps as $learningStep) {
                        $step = $learningPathApiClient->fetchLearningStep($learningPath->id, $learningStep->id);
                        LearningPathStep::updateOrCreate(
                            [
                                'id' => $learningStep->id,
                                'learning_path_id' => $learningPath->id,
                            ],
                            [
                                'title' => $learningStep->title->title ?? '',
                                'order' => $learningStep->seqNo,
                                'json' => $step,
                            ]
                        );
                    }
                } catch (ClientException $e) {
                    // Keep on trucking!
                }
            }
        }

        return redirect(route('admin.learningpath.index'));
    }

    public function show(Request $request, $id)
    {
        $learningPath = LearningPath::findOrFail($id);

        return view('admin.learningpath-import.show')->with(compact('learningPath'));
    }
}
