<?php


namespace App\Http\Controllers\API;


use App\Content;
use App\Events\ResourceSaved;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use Illuminate\Support\Facades\Log;

class PublishResourceController extends Controller
{
    public function publishResource($resourceId)
    {
        $adapter = app(H5PAdapterInterface::class);
        if ($adapter->enableDraftLogic()) {
            $resource = Content::findContentById($resourceId);
            if ($resource && !$resource->is_published) {
                $resource->is_published = true;
                $resource->save();

                event(new ResourceSaved($resource->getEdlibDataObject()));
            }
        } else {
            Log::error('Failed to set published state. Feature is disabled.');
            \App::abort(500);
        }
    }
}
