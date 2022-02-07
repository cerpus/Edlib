<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DokuRequest extends FormRequest
{
    public function authorize(AuthManager $auth): bool
    {
        return true;

//        $doku = $this->get('doku');
//        assert($doku instanceof Doku);
//
//        return $doku->creator_id === $this->user()->id;
    }

    public function rules(): array
    {
        $creating = $this->isMethod('POST');

        return [
            'data' => ['array', Rule::requiredIf($creating)],
            'title' => ['string', Rule::requiredIf($creating)],
        ];
    }
}
