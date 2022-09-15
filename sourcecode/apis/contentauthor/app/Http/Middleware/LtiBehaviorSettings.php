<?php

namespace App\Http\Middleware;

use App\Http\Requests\LTIRequest;
use App\SessionKeys;
use Cerpus\CoreClient\DataObjects\BehaviorSettingsDataObject;
use Cerpus\CoreClient\DataObjects\EditorBehaviorSettingsDataObject;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Validator;

class LtiBehaviorSettings
{
    /*
     * Extract Behavior settings from a LTI request, validate and add to Session if valid
     */
    public function handle(Request $request, Closure $next, $context = null)
    {
        $ltiRequest = LTIRequest::fromRequest($request);
        if ($ltiRequest && $ltiRequest->getExtBehaviorSettings()) {
            $extBehaviorSettings = json_decode($ltiRequest->getExtBehaviorSettings(), true);

            if ($context === 'view') {
                $validator = Validator::make($extBehaviorSettings, BehaviorSettingsDataObject::$rules);
                $registerSettings = function ($behaviorSettings) {
                    Session::flash(SessionKeys::EXT_BEHAVIOR_SETTINGS, BehaviorSettingsDataObject::create($behaviorSettings));
                };
            } elseif ($context === 'editor') {
                $listEntry = $request->get('redirectToken');
                if (empty($listEntry)) {
                    $listEntry = Uuid::uuid4()->toString();
                    $request->request->add(['redirectToken' => $listEntry]);
                }

                $validator = Validator::make($extBehaviorSettings, EditorBehaviorSettingsDataObject::$rules);
                $registerSettings = function ($editorBehaviorSettings) use ($listEntry) {
                    $editorSettings = EditorBehaviorSettingsDataObject::create($editorBehaviorSettings);
                    if (!empty($editorBehaviorSettings['behaviorSettings'])) {
                        $behaviorSettings = BehaviorSettingsDataObject::create($editorBehaviorSettings['behaviorSettings']);
                        $editorSettings->setBehaviorSettings($behaviorSettings);
                        Session::flash(SessionKeys::EXT_BEHAVIOR_SETTINGS, $behaviorSettings);
                    }
                    Session::put(sprintf(SessionKeys::EXT_EDITOR_BEHAVIOR_SETTINGS, $listEntry), $editorSettings);
                };
            }
            if ($validator->fails()) {
                Log::error("Validation of ext_behavior_settings LTI param failed. Errors:", $validator->messages()->getMessages());
            } else {
                $registerSettings($extBehaviorSettings);
            }
        }

        return $next($request);
    }
}
