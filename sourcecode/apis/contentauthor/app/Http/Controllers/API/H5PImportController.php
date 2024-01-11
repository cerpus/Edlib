<?php

namespace App\Http\Controllers\API;

use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\Exceptions\H5pImportException;
use App\H5PContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\H5PImportRequest;
use App\Libraries\H5P\H5PImport;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use H5PStorage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class H5PImportController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function importH5P(H5PImportRequest $request, H5PImport $import, H5PStorage $storage, H5PAdapterInterface $adapter)
    {
        $uploadedFile = $request->file('h5p');

        try {
            $response = $import->import($uploadedFile, $storage, $request->input('userId'), $request->input('isDraft'), !$request->input('isPublic', $adapter->getDefaultImportPrivacy()));
        } catch (H5pImportException $e) {
            throw new BadRequestHttpException($e->getMessage(), previous: $e);
        }

        $h5pContent = H5PContent::find($response->h5pId);
        if ($request->input('disablePublishMetadata', true) === true) {
            config([
                'feature.enableUserPublish' => false,
            ]);
        }

        event(new H5PWasSaved($h5pContent, $request, ContentVersion::PURPOSE_IMPORT));

        return response()->json($response->toArray())->setStatusCode(Response::HTTP_CREATED);
    }
}
