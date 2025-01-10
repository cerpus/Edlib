<?php

namespace App\Http\Controllers\Admin;

use App\Gametype;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameUploadRequest;
use App\Libraries\Games\Millionaire\AppManifest;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\StorageAttributes;
use League\Flysystem\Visibility;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

use const JSON_THROW_ON_ERROR;

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
        $zipFile = new Filesystem(
            new ZipArchiveAdapter(
                new FilesystemZipArchiveProvider($gameFile->path()),
            ),
        );

        $appManifest = new AppManifest(json_decode(
            $zipFile->read('MILLIONAIRE/appmanifest.json'),
            flags: JSON_THROW_ON_ERROR,
        ));
        $version = $appManifest->getVersion();

        $manager = new MountManager([
            'zip' => $zipFile,
            'uploads' => Storage::disk()->getDriver(),
        ], [
            Config::OPTION_VISIBILITY => Visibility::PUBLIC,
        ]);

        collect($manager->listContents('zip://MILLIONAIRE/', deep: true))
            ->filter(fn(StorageAttributes $file): bool => $file->isFile())
            ->each(function (StorageAttributes $file) use ($manager, $version) {
                $dest = preg_replace(
                    '@^zip://MILLIONAIRE/@i',
                    'uploads://games/millionaire/' . $version . '/',
                    $file->path(),
                );

                $manager->copy($file->path(), $dest);
            });

        // update database
        $gameType = Gametype::ofGameType(
            $appManifest->getName(),
            $appManifest->getMajorVersion(),
            $appManifest->getMinorVersion(),
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

        return redirect('/admin/games')->with(
            'message',
            $gameType->title . ' v' . $appManifest->getVersion() . ' successfully ' . $action . '.',
        );
    }
}
