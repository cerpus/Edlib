<?php

namespace App\Http\Controllers\Admin;

use App\H5PContent;
use App\Http\Controllers\Controller;
use App\Jobs\ReplaceVideoRefId;
use Illuminate\Http\Request;

class NDLAReplaceRefController extends Controller
{
    public function index(Request $request)
    {
        $total = \DB::table("h5p_refid_support")->count();
        $processed = \DB::table("h5p_refid_support")->where('processed', 1)->count();
        $targets = \DB::table("h5p_refid_support")->where('istarget', 1)->count();
        $targetList = \DB::table("h5p_refid_support")->where('istarget', 1)->get();
        return view('admin.video.replaceref', compact('total', 'processed', 'targets', 'targetList'));
    }

    public function reindex()
    {
        \DB::table('h5p_refid_support')->delete();
        return redirect(route('admin.video.ndla.replaceref'));
    }

    public function showTargets()
    {
        return redirect(route('admin.video.ndla.replaceref'));
    }

    public function populateTable()
    {
        \DB::table('h5p_contents')
            ->whereNotIn('id', function ($query) {
                $query->select('content_id')->from('h5p_refid_support');
            })
            ->chunkById(200, function ($contents) {
                foreach ($contents as $content) {
                    $isTarget = preg_match('/https?:\\\\\/\\\\\/bc\\\\\/ref:\d+/', $content->parameters);
                    \DB::table('h5p_refid_support')->insert([
                        'content_id' => $content->id,
                        'title' => $content->title,
                        'isTarget' => $isTarget,
                    ]);
                }
            });
        return redirect(route('admin.video.ndla.replaceref'));
    }

    public function doReplaceRef()
    {
        \DB::table("h5p_refid_support")
            ->where('processed', 0)
            ->where('istarget', 1)
            ->chunkById(50, function ($targets) {
                foreach ($targets as $target) {
                    ReplaceVideoRefId::dispatch(H5PContent::find($target->content_id));
                    \DB::table('h5p_refid_support')
                        ->where('content_id', $target->content_id)
                        ->update(['processed' => 1]);
                }
            });

        return redirect(route('admin.video.ndla.replaceref'));
    }
}
