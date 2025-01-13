<?php

namespace App\Http\Controllers\Admin;

use App\H5PContent;
use Illuminate\Support\Str;
use App\Content;
use App\ContentLock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocksController extends Controller
{
    public function index()
    {
        $allLocks = ContentLock::select(['content_id', 'email', 'name', 'updated_at'])->orderBy('updated_at', 'ASC')->get();

        $locks = [];

        foreach ($allLocks as $aLock) {
            $content = Content::findContentById($aLock->content_id);
            $type = (!$content instanceof H5PContent) ? Str::ucfirst($content->getContentType()) : Str::upper($content->getContentType());
            $locks[] = (object) [
                'id' => $aLock->content_id,
                'title' => sprintf("[%s] %s", $type, $content->title),
                'name' => $aLock->name ?? '-',
                'email' => $aLock->email ?? '-',
                'locked_to' => $aLock->updated_at->addMinutes(60)->setTimeZone("Europe/Oslo")->toDateTimeLocalString() ?? '-',
            ];
        }

        return view("admin.locks.index")->with(compact('locks'));
    }

    public function destroy(Request $request)
    {
        if ($lock = ContentLock::find($request->input('lock_id'))) {
            if ($status = $lock->delete()) {
                $request->session()->flash('message', 'Removed lock.');
            } else {
                $request->session()->flash('message', 'Could not remove lock.');
            }
        } else {
            $request->session()->flash('message', 'Lock was not found. It may have expired.');
        }


        return redirect(route('admin.locks'));
    }
}
