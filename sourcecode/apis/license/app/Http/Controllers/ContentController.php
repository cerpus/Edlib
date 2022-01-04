<?php

namespace App\Http\Controllers;


use App\Models\Content;
use App\Models\ContentLicense;
use App\Models\Resources\ContentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContentController extends Controller
{
    public function getContent()
    {
        $contentCollection = Content::all();

        return ContentResource::collection($contentCollection);

        /*
        $json = [];
        foreach ($contentCollection as $content) {
            $json[] = $this->contentJson($content);
        }

        return $json;
        */
    }

    public function getChangedContent(Request $request)
    {
        $timestamp = $request->input('after');
        $limtime = date('Y-m-d H:i:s', $timestamp);

        $contentCollection = Content::where('created_at', ">=", $limtime)->orWhere('updated_at', '>=', $limtime)->get();

        return ContentResource::collection($contentCollection);
        /*
        $json = [];

        foreach ($contentCollection as $content) {
            $json[] = $this->contentJson($content);
        }

        return $json;
        */
    }

    public function addContent(Request $request)
    {
        $this->validate($request, [
            'site' => 'required|string|max:256',
            'content_id' => 'required|string|max:1024',
            'name' => 'required|string',
        ]);

        $obj = new Content();
        $obj->site = $request->input("site");
        $obj->content_id = $request->input("content_id");
        $obj->content_id_hash = sha1($obj->content_id, false);
        $obj->name = $request->input("name");
        $obj->save();

        return new ContentResource($obj);
    }

    public function getContentById($id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response("Not found", Response::HTTP_NOT_FOUND);
        }

        return new ContentResource($content);
    }

    public function deleteContent($id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response("Not found", Response::HTTP_NOT_FOUND);
        }

        $content->delete();

        return ['status' => 'ok'];
    }

    public function setLicenses(Request $request, $id)
    {
        $licenses = $request->input('licenses');

        $content = Content::find($id);

        if (!$content) {
            return response("Not found", Response::HTTP_NOT_FOUND);
        }
        $content->touch();

        if ($licenses) {
            foreach ($content->licenses as $license) {
                if (in_array($license->license_id, $licenses)) {
                    unset($licenses[array_search($licenses, $license->license_id)]);
                } else {
                    $license->delete();
                }
            }
            foreach ($licenses as $license_id) {
                $license = new ContentLicense();
                $license->content_id = $id;
                $license->license_id = $license_id;
                $license->save();
            }
        } else {
            foreach ($content->licenses as $license) {
                $license->delete();
            }
        }

        return $this->getContentById($id);
    }

    public function addLicense($id, $license_id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response("Not found", Response::HTTP_NOT_FOUND);
        }

        foreach ($content->licenses as $license) {
            if ($license->license_id == $license_id) {
                return response('Conflict', Response::HTTP_CONFLICT);
            }
        }

        $content->touch();

        $license = new ContentLicense();
        $license->content_id = $id;
        $license->license_id = $license_id;
        $license->save();

        return $this->getContentById($id);
    }

    public function removeLicense($id, $license_id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response("Not found", Response::HTTP_NOT_FOUND);
        }

        foreach ($content->licenses as $license) {
            if ($license->license_id == $license_id) {
                $content->touch();
                $license->delete();
                return $this->getContentById($id);
            }
        }

        return response('Conflict', Response::HTTP_CONFLICT);
    }
}
