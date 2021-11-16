<?php

namespace App\Http\Controllers;

use App\Traits\LtiUrlFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\SessionKeys;
use Ramsey\Uuid\Uuid;
use App\Libraries\BasicLTI;

class ContentExplorerController extends Controller
{
    use LtiUrlFunctions;
    /**
     * @return string
     * @throws \Exception
     */
    public function insertResource()
    {
        $resourceId = Uuid::uuid4()->toString();
        $lti = new BasicLTI(config('core.key'), config('core.secret'));
        $lti->setLaunchUrlFromXML(config('core.server') . '/lti/selection/launch.xml');
        $ltiParams = [];
        $ltiParams['launch_presentation_return_url'] = route('lti.return', $resourceId);
        $ltiParams['ext_content_return_url'] = route('lti.return', $resourceId);
        $ltiParams['selection_directive'] = true;
        $ltiParams['ext_content_return_types'] = 'lti_launch_url';
        $ltiParams['ext_read_only'] = 1;
        $filter = new \stdClass();
        $filter->source = new \stdClass();
        $filter->source->blacklist = new \stdClass();
        $filter->source->blacklist->value = array('Article');
        $ltiParams['ext_content_filter'] = json_encode($filter);
        $lti->setExtraLti($ltiParams);
        return $lti->getForm();
    }

    public function container()
    {
        return view('lti.container');
    }

    public function returnUrl(Request $request, $id)
    {
        return view('lti.return', ['inputs' => $request->all(), 'resourceId' => $id]);
    }

    public function launch(Request $request)
    {
        $lti = new BasicLTI(config('core.key'), config('core.secret'));
        $launchUrl = $this->launchUrl($this->launchId($request->get('url')));

        $behaviorSettings = Session::get(SessionKeys::EXT_BEHAVIOR_SETTINGS);
        if( !empty($behaviorSettings)) {
            $lti->setExtraLti([
                'ext_behavior_settings' => $behaviorSettings->toJson(),
            ]);
            Session::keep(SessionKeys::EXT_BEHAVIOR_SETTINGS);
        }
        $customCSS = Session::get(SessionKeys::EXT_CSS_URL);
        if( !empty($customCSS)){
            $lti->setExtraLti([
                'launch_presentation_css_url' => $customCSS
            ]);
        }
        $lti->setLaunchUrl($launchUrl);
        $lti->setReturnPoint(route('lti.return', 'view'));
        return $lti->getForm();
    }
}
