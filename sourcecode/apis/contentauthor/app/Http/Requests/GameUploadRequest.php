<?php

namespace App\Http\Requests;

use Illuminate\Http\UploadedFile;
use Auth;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\Filesystem;
use Illuminate\Foundation\Http\FormRequest;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class GameUploadRequest extends FormRequest
{
    /**
     * If the user is logged into admin it's OK to let him upload game files
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'gameFile' => 'bail|required|file|mimes:zip',
        ];
    }

    /*
     * We now know the user uploaded a file, but we have some additional requirements
     * 1. It is a zip file
     * 2. It contains an MILLIONAIRE/appmanifest.json file
     * 3. The MILLIONAIRE/appmanifest.json is a valid json file
     * 4. The decoded MILLIONAIRE/appmanifest.json has a 'description' property that we
     *    will use to extract version information
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $gameFile = $validator->getData()['gameFile'];
            assert($gameFile instanceof UploadedFile);

            $zipFile = new Filesystem(
                new ZipArchiveAdapter(
                    new FilesystemZipArchiveProvider($gameFile->path()),
                ),
            );

            if (!$zipFile->has('MILLIONAIRE/appmanifest.json')) {
                $validator->errors()->add(
                    'gameFile',
                    "Missing file 'MILLIONAIRE/appmanifest.json' in zip archive.",
                );
                return;
            }

            $appManifest = json_decode($zipFile->read('MILLIONAIRE/appmanifest.json'));
            if (json_last_error() !== JSON_ERROR_NONE) {
                $validator->errors()->add('gameFile', "'MILLIONAIRE/appmanifest.json' is not a valid json file.");
                return;
            }

            if (!property_exists($appManifest, 'description')) {
                $validator->errors()->add(
                    'gameFile',
                    "The appmanifest.json is missing the description property. The description property is used to read the version information.",
                );
            }
        });
    }
}
