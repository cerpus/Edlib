<?php

namespace App\Http\Controllers\Admin;

use App\Libraries\DataObjects\ContentStorageSettings;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Gametype;
use League\Flysystem\Filesystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameUploadRequest;
use App\Libraries\Games\Millionaire\AppManifest;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class GamesAdminController extends Controller
{
    public function index()
    {
        $gameTypes = Gametype::orderBy('name')
            ->orderBy('major_version')
            ->orderBy('minor_version', 'desc')
            ->get();

        return view('admin.games.index')->with(compact('gameTypes'));
    }

    public function store(GameUploadRequest $request)
    {
        $gameFile = $request->file('gameFile');
        $zipFile = new Filesystem(new ZipArchiveAdapter($gameFile->path()));

        $appManifest = new AppManifest(json_decode($zipFile->read('MILLIONAIRE/appmanifest.json')));

        $version = $appManifest->getVersion();
        $extractPath = '/tmp/millionaire/' . $version;

        $zip = new ZipArchive();
        if ($zip->open($gameFile->path())) {
            $zip->extractTo($extractPath);
            $zip->close();
        }

        $extractedFiles = Storage::disk('tmp')->allFiles('millionaire/' . $version);

        // Copy files to game uploads disk
        collect($extractedFiles)->each(function ($file) {
            $path = sprintf(ContentStorageSettings::GAMES_PATH, $file);
            Storage::disk()->put($path, Storage::disk('tmp')->get($file));
        });

        // update database
        $gameType = Gametype::ofGameType(
            $appManifest->getName(),
            $appManifest->getMajorVersion(),
            $appManifest->getMinorVersion()
        )->first();

        $action = 'updated';
        if (!$gameType) {
            $gameType = new Gametype();
            $action = 'installed';
        }

        $gameType->title = $appManifest->getTitle();
        $gameType->name = $appManifest->getName();
        $gameType->major_version = $appManifest->getMajorVersion();
        $gameType->minor_version = $appManifest->getMinorVersion();
        $gameType->save();
        $gameType->touch();

        // Clean up temp files
        Storage::disk('tmp')->deleteDirectory('millionaire/' . $version);

        return redirect('/admin/games')->with(
            'message',
            $gameType->title . ' v' . $appManifest->getVersion() . ' successfully ' . $action . '.'
        );
    }
}
