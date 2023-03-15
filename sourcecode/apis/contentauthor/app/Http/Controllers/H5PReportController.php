<?php

namespace App\Http\Controllers;

use App\Libraries\H5P\Reports\H5PPackage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class H5PReportController extends Controller
{
    public function questionAndAnswer(Request $request, H5PPackage $package)
    {
        $contexts = $request->get('contexts');
        if (is_null($contexts)) {
            return response()->json([
                'error' => "Missing contexts",
            ], Response::HTTP_BAD_REQUEST);
        }
        if (is_string($contexts)) {
            $contexts = explode(',', $contexts);
        }

        $userId = $request->get("userId", false);

        $elements = $package->questionsAndAnswers($contexts, $userId);

        $returnValues = collect($elements)
            ->map(function ($element) {
                return [
                    'context' => $element['context'],
                    'elements' => $this->flattenElements($element['elements']),
                ];
            })->reject(function ($item) {
                return empty($item['elements']);
            })->values();

        return response()->json($returnValues);
    }

    private function flattenElements($elements)
    {
        $return = [];
        if ($elements['composedComponent'] === true) {
            foreach ($elements['elements'] as $element) {
                if ($element['composedComponent'] === true) {
                    $return = array_merge($return, $this->flattenElements($element));
                } else {
                    $returnElement = [
                        'text' => $element['question'],
                        'answer' => $element['answer'],
                        "answers" => $element["answers"] ?? [],
                    ];

                    $returnElement = $this->updateOrCreateAnswerType($element, $returnElement);

                    $return[] = $returnElement;
                }
            }
            return $return;
        } else {
            $returnElement = [
                'text' => $elements['question'],
                'answer' => $elements['answer'],
                'answers' => $elements['answers'] ?? [],
            ];

            $returnElement = $this->updateOrCreateAnswerType($elements, $returnElement);

            return [$returnElement];
        }
    }


    private function updateOrCreateAnswerType($elements, array $answer): array
    {
        if ($elements["type"] ?? null) {
            $answer["type"] = $elements["type"];
        }

        if ($elements["short_type"] ?? null) {
            $answer["type"] = $elements["short_type"];
        }

        return $answer;
    }
}
