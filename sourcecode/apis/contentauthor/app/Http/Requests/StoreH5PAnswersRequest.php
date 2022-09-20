<?php

namespace App\Http\Requests;

use App\User;

class StoreH5PAnswersRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->session()->has("userId");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->input("action")) {
            case "h5p_contents_user_data":
                $rules = [
                    'content_id' => 'required|numeric',
                    'data_type' => 'required|string',
                    'sub_content_id' => 'required|numeric',
                    'data' => 'required',
                    'preload' => 'required|numeric',
                    'invalidate' => 'required|numeric',
                    'action' => 'required|string',
                ];

                if (!$this->isPostValuesPresentInRequest(['invalidate','preload','data'])) {
                    $rules['missing'] = 'required';
                }

                break;
            case "h5p_setFinished":
                $rules = [
                    'contentId' => 'required|numeric',
                    'score' => 'required',
                    'maxScore' => 'required',
                    'opened' => 'numeric',
                    'finished' => 'numeric',
                ];

                if (!$this->isPostValuesPresentInRequest(array_keys($rules))) {
                    $rules['missing'] = 'required';
                }
                break;
            case 'h5p_preview':
                $rules = [];
                break;
            default:
                throw new \Exception("Invalid action");
        }


        return $rules;
    }

    private function isPostValuesPresentInRequest($postValues)
    {
        return count(array_intersect_key(array_flip($postValues), $this->request->all())) >= count($postValues);
    }
}
