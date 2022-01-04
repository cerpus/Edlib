<?php

namespace App;

use App\Http\Requests\LTIRequest;
use Illuminate\Support\Facades\Request;

class H5pLti {
    private $consumerKey;
    private $consumerSecret;

    public function __construct()
    {
        $this->consumerKey = config("app.consumer-key", "h5p");
        $this->consumerSecret = config("app.consumer-secret", "secretnotspec");
    }

    public function validatedLtiRequestOauth($ltiRequest) {
        return $ltiRequest->validateOauth10($this->consumerKey, $this->consumerSecret);
    }

    public function getLtiRequest() {
        if (isset($_POST['lti_message_type'])) {
            $ltiRequest = LTIRequest::current();
            if ($ltiRequest->validateOauth10($this->consumerKey, $this->consumerSecret)) {
                $this->ltiRequest = $ltiRequest;
                return $ltiRequest;
            }
        }
        return null;
    }
}
