<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BulkSyncWithMetadata;
use Illuminate\Http\Request;

class MetadataController extends Controller
{
    public function sync()
    {
        return view('admin.metadata.sync');
    }

    public function doSync(Request $request)
    {
        BulkSyncWithMetadata::dispatch();

        $request->session()->flash("message", "Indexing is starting");

        return redirect(route("admin.metadataservice.sync"));
    }
}
