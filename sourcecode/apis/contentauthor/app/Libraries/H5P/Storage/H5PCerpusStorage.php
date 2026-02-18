<?php

namespace App\Libraries\H5P\Storage;

use App\H5PContentsVideo;
use App\H5PFile;
use App\H5PLibrary;
use App\Jobs\PingVideoApi;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PDownloadInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use Exception;
use H5PFileStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use League\Flysystem\StorageAttributes;
use Psr\Log\LoggerInterface;

class H5PCerpusStorage implements H5PFileStorage, H5PDownloadInterface, CerpusStorageInterface
{
    private Filesystem $filesystem;
    private Filesystem $uploadDisk;
    private string $diskName;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly H5PVideoInterface $videoAdapter,
    ) {
        $this->filesystem = Storage::disk();
        $this->diskName = Storage::getDefaultDriver();
        $this->uploadDisk = Storage::disk('h5pTmp');
    }

    private function triggerVideoConvert($fromId, $toId, $file)
    {
        $sourcepath = $this->getFilePrefix($fromId) . $file;

        $h5pFile = H5PFile::where('external_reference', $sourcepath)->first();
        $hash = $h5pFile->file_hash ?? null;

        if (!$h5pFile && $this->filesystem->has($sourcepath)) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'h5p-');
            file_put_contents($tmpfile, $this->filesystem->readStream($sourcepath));
            $hash = md5_file($tmpfile);
            unlink($tmpfile);
        }

        $uploadJsonData = $this->videoAdapter->upload($this->filesystem->readStream($sourcepath), $hash);

        $h5pContentsVideo = H5PContentsVideo::firstOrCreate([
            'h5p_content_id' => $toId,
            'video_id' => $uploadJsonData->videoId,
            'source_file' => $file,
        ]);

        PingVideoApi::dispatch($h5pContentsVideo);
    }

    private function getFilePrefix($contentId)
    {
        return $contentId === 'editor' || empty($contentId) ? ContentStorageSettings::EDITOR_PATH : sprintf(ContentStorageSettings::CONTENT_PATH, $contentId);
    }

    /**
     * @param string $file
     * @param int|string $fromId
     * @param int $toId
     * @throws Exception
     */
    public function cloneContentFile($file, $fromId, $toId)
    {
        $fromPath = $this->getFilePrefix($fromId) . $file;
        $toPath = $this->getFilePrefix($toId) . $file;
        if ($fromPath === $toPath) {
            return;
        }

        $path = explode('/', $file);
        $type = $path[0];

        if (config('h5p.video.enable') === true && strtolower($type) === "videos") {
            $this->triggerVideoConvert($fromId, $toId, $file);
        }

        $pendingFile = H5PFile::ofFileUploadFromContent($toId)->where('filename', $file)->get()->isNotEmpty();
        if (!$pendingFile && $this->filesystem->exists($fromPath) && $this->filesystem->missing($toPath)) {
            $result = $this->filesystem->copy($fromPath, $toPath);
            if (!$result) {
                throw new Exception("Couldn't copy file '$file' from '$fromPath' to '$toPath'");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function saveLibrary($library)
    {
        $library['patchVersionInFolderName'] = true;
        $path = sprintf(ContentStorageSettings::LIBRARY_PATH, \H5PCore::libraryToFolderName($library));
        $libraryPath = Str::after($library['uploadDirectory'], $this->uploadDisk->path(""));
        $this->deleteLibraryFromPath($path);

        $this->copyLibrary($libraryPath, $path);
    }

    /**
     * @return bool
     */
    private function deleteLibraryFromPath($library)
    {
        return $this->filesystem->deleteDirectory($library);
    }

    /**
     * @param string $source
     * @param string $destination
     *
     * @throws Exception Unable to copy the file
     */
    public function copyLibrary($source, $destination)
    {
        $ignoredFiles = $this->getIgnoredFiles("{$source}/.h5pignore");

        collect($this->uploadDisk->listContents($source, true))
            ->filter(function (StorageAttributes $fileProperties) use ($ignoredFiles) {
                $file = basename($fileProperties->path());
                return ($file != '.') && ($file != '..') && $file != '.git' && $file != '.gitignore' && !in_array($file, $ignoredFiles); //TODO check directories recursively
            })
            ->each(function (StorageAttributes $fileProperties) use ($destination, $source) {
                if (!$fileProperties->isDir()) {
                    $file = Str::after($fileProperties->path(), $source);
                    $this->filesystem->writeStream("{$destination}/{$file}", $this->uploadDisk->readStream("{$source}{$file}"));
                }
            });
    }

    /**
     * Retrieve array of file names from file.
     *
     * @param string $file
     * @return array Array with files that should be ignored
     */
    private function getIgnoredFiles($file)
    {
        $contents = $this->uploadDisk->get($file);
        if ($contents === null) {
            return [];
        }

        return preg_split('/\s+/', $contents);
    }

    /**
     * @inheritDoc
     */
    public function saveContent($source, $content)
    {
        $path = Str::after($source, $this->uploadDisk->path(""));
        collect($this->uploadDisk->listContents($path, true))
            ->filter(function (StorageAttributes $file) {
                return $file->isFile() && !in_array(basename($file->path()), ['content.json']);
            })
            ->each(function (StorageAttributes $file) use ($path, $content) {
                $localPath = Str::after($file->path(), $path);
                $filePath = preg_replace('#/+#', '/', sprintf(ContentStorageSettings::CONTENT_PATH, $content['id']) . $localPath);
                $this->filesystem->put($filePath, $this->uploadDisk->readStream($file->path()));
            });
    }

    /**
     * @inheritDoc
     */
    public function deleteContent($content)
    {
        Log::info(sprintf("A user somehow ended here. The content id '%s'", $content['id'] ?? "not set"));
        throw new Exception("This action is not implemented.");
    }

    /**
     * @inheritDoc
     */
    public function cloneContent($id, $newId)
    {
        $from = $this->getFilePrefix($id);
        $allFiles = $this->filesystem->allFiles($from);
        foreach ($allFiles as $filepath) {
            $to = $this->getFilePrefix($newId) . Str::after($filepath, $from);
            if ($from !== $to) {
                $this->filesystem->copy($filepath, $to);
            }
        }
    }

    /**
     * @inheritDoc
     * @throws FilesystemException
     */
    public function getTmpPath()
    {
        $this->uploadDisk->createDirectory(ContentStorageSettings::TEMP_DIR);
        $path = sprintf(ContentStorageSettings::TEMP_PATH, uniqid('h5p-'));
        return $this->uploadDisk->path($path);
    }

    /**
     * @inheritDoc
     */
    public function exportContent($id, $target)
    {
        $contentPath = sprintf(ContentStorageSettings::CONTENT_PATH, $id);
        if (!$this->filesystem->has($contentPath)) {
            $localPath = Str::after($target, $this->uploadDisk->path(""));
            $this->uploadDisk->makeDirectory($localPath);
        }

        collect($this->filesystem->allFiles($contentPath))
            ->each(function ($file) use ($contentPath, $target) {
                $localPath = Str::after($target, $this->uploadDisk->path("")) . "/" . Str::after($file, $contentPath);
                $this->uploadDisk->put($localPath, $this->filesystem->readStream($file));
            });
    }

    /**
     * @inheritDoc
     */
    public function exportLibrary($library, $target)
    {
        $folder = H5PLibrary::libraryToFolderName($library);
        // To make the exported file backward compatible, we don't use patch in target folder name
        $targetFolder = H5PLibrary::libraryToFolderName($library, false);
        $srcPath = sprintf(ContentStorageSettings::LIBRARY_PATH, $folder);
        $finalTarget = Str::after($target, $this->uploadDisk->path("")) . "/$targetFolder";
        if ($this->hasLibraryVersion($folder, sprintf(ContentStorageSettings::LIBRARY_VERSION_PREFIX, $library['majorVersion'], $library['minorVersion'], $library['patchVersion']))) {
            $this->exportLocalDirectory($srcPath, $finalTarget, $folder);
        } else {
            $this->exportRemoteDirectory($srcPath, $finalTarget, $folder);
        }
    }

    private function exportLocalDirectory($directory, $target, $folder)
    {
        collect($this->uploadDisk->allFiles($directory))
            ->each(function ($file) use ($target, $folder) {
                $localPath = $target . Str::after($file, $folder);
                if ($this->uploadDisk->missing($localPath)) {
                    $this->uploadDisk->copy($file, $localPath);
                }
            });
    }

    private function exportRemoteDirectory($directory, $target, $folder)
    {
        $libraries = collect($this->filesystem->directories(ContentStorageSettings::LIBRARY_DIR))
            ->filter(function ($library) use ($folder) {
                return $library === sprintf(ContentStorageSettings::LIBRARY_PATH, $folder);
            });
        if ($libraries->isEmpty()) {
            throw new Exception("Library not found");
        }
        collect($this->filesystem->allFiles($directory))
            ->each(function ($file) use ($target, $folder) {
                $localPath = $target . Str::after($file, $folder);
                $this->uploadDisk->put($localPath, $this->filesystem->readStream($file));
            });
    }

    /**
     * @inheritDoc
     */
    public function saveExport($source, $filename)
    {
        $this->deleteExport($filename);
        $this->filesystem->putFileAs(ContentStorageSettings::EXPORT_DIR, $source, $filename);
    }

    /**
     * @inheritDoc
     */
    public function deleteExport($filename)
    {
        if ($this->hasExport($filename)) {
            $this->filesystem->delete(sprintf(ContentStorageSettings::EXPORT_PATH, $filename));
        }
    }

    /**
     * @inheritDoc
     */
    public function hasExport($filename)
    {
        return $this->filesystem->exists(sprintf(ContentStorageSettings::EXPORT_PATH, $filename));
    }

    /**
     * @inheritDoc
     */
    public function cacheAssets(&$files, $key)
    {
        $checkedLibraries = collect();
        foreach ($files as $type => $assets) {
            if (empty($assets)) {
                continue; // Skip no assets
            }

            $content = '';
            foreach ($assets as $asset) {
                $library = collect(explode("/", $asset->path))
                    ->filter(function ($element) {
                        return \H5PCore::libraryFromString($element);
                    })
                    ->first();
                if ($checkedLibraries->has($library) || $this->hasLibraryVersion($asset->path, $asset->version)) {
                    $assetContent = $this->uploadDisk->get($asset->path);
                    $checkedLibraries->put($library, true);
                } else {
                    $assetContent = $this->filesystem->get($asset->path);
                }
                // Get file content and concatenate
                if ($type === 'scripts') {
                    $content .= $assetContent . ";\n";
                    $filePath = ContentStorageSettings::CACHEDASSETS_JS_PATH;
                } else {
                    // Rewrite relative URLs used inside stylesheets
                    $cssRelPath = preg_replace('/[^\/]+$/', '', $asset->path);
                    $content .= preg_replace_callback(
                        '/url\([\'"]?([^"\')]+)[\'"]?\)/i',
                        function ($matches) use ($cssRelPath) {
                            if (preg_match("/^(data:|([a-z0-9]+:)?\/)/i", $matches[1]) === 1) {
                                return $matches[0]; // Not relative, skip
                            }
                            return 'url("../' . $cssRelPath . $matches[1] . '")';
                        },
                        $assetContent,
                    ) . "\n";
                    $filePath = ContentStorageSettings::CACHEDASSETS_CSS_PATH;
                }
            }

            $outputfile = sprintf($filePath, $key);
            if (!$this->filesystem->put($outputfile, $content)) {
                throw new Exception("Could not create cached asset");
            }
            $files[$type] = [(object) [
                'path' => $outputfile,
                'version' => '',
                'url' => $this->filesystem->url($outputfile),
            ]];
        }
    }

    /**
     * @inheritDoc
     */
    public function getCachedAssets($key)
    {
        $files = [];
        foreach ([
            'scripts' => ContentStorageSettings::CACHEDASSETS_JS_PATH,
            'styles' => ContentStorageSettings::CACHEDASSETS_CSS_PATH,
        ] as $type => $path) {
            $file = sprintf($path, $key);
            if ($this->filesystem->has($file)) {
                $files[$type] = [(object) [
                    'path' => $file,
                    'version' => '',
                    'url' => $this->filesystem->url($file),
                ]];
            }
        }
        return empty($files) ? null : $files;
    }

    /**
     * @inheritDoc
     */
    public function deleteCachedAssets($keys)
    {
        foreach ($keys as $hash) {
            foreach ([
                ContentStorageSettings::CACHEDASSETS_JS_PATH,
                ContentStorageSettings::CACHEDASSETS_CSS_PATH,
            ] as $path) {
                $file = sprintf($path, $hash);
                if ($this->filesystem->has($file)) {
                    $this->filesystem->delete($file);
                }
            }
        }
    }

    /**
     * @inheritDoc
     * @deprecated
     */
    public function getContent($file_path)
    {
        // Seemingly deprecated from H5P
    }

    /**
     * @param \H5peditorFile $file
     * @param int $contentId
     * @return \H5peditorFile
     * @throws Exception
     */
    public function saveFile($file, $contentId)
    {
        $path = sprintf(ContentStorageSettings::FILE_PATH, $this->getFilePrefix($contentId), $file->getType());
        $uploadedFile = new UploadedFile($_FILES['file']['tmp_name'], $file->getName(), $file->mime);
        $result = $uploadedFile->storeAs($path, $file->getName(), $this->diskName);
        if (!$result) {
            throw new Exception(sprintf("Could not store the file '%s'", $uploadedFile->getFilename()));
        }
        $file->name = $result;
        $file->path = $uploadedFile->path();
        $file->mime = $uploadedFile->getMimeType();
        $file->hash = md5_file($uploadedFile->path());

        return $file;
    }

    /**
     * @inheritDoc
     */
    public function moveContentDirectory($source, $contentId = null)
    {
        if ($source === null) {
            return null;
        }

        $target = $this->getFilePrefix($contentId);

        $localPath = Str::after($source, $this->uploadDisk->path(ContentStorageSettings::TEMP_DIR));
        $contentSource = sprintf(ContentStorageSettings::TEMP_CONTENT_PATH, $localPath);

        collect($this->uploadDisk->listContents($contentSource, true))
            ->filter(function ($file) {
                return $file->isFile() && !in_array(basename($file->path()), ['content.json']);
            })
            ->each(function ($file) use ($contentSource, $target) {
                $this->filesystem->put($target . Str::after($file['path'], $contentSource), $this->uploadDisk->readStream($file['path']));
            });
    }

    /**
     * @inheritDoc
     */
    public function getContentFile($file, $contentId)
    {
        $path = $this->getFilePrefix($contentId) . $file;
        return $this->filesystem->exists($path) ? $path : null;
    }

    /**
     * @inheritDoc
     */
    public function removeContentFile($file, $contentId)
    {
        $path = $this->getFilePrefix($contentId) . $file;
        if ($this->filesystem->exists($path)) {
            $this->filesystem->delete($path);
        }
        H5PFile::deleteContentPendingUpload($contentId, $path);
    }

    /**
     * @inheritDoc
     */
    public function hasWriteAccess()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function hasPresave($libraryName, $developmentPath = null)
    {
        $path = sprintf(ContentStorageSettings::PRESAVE_SCRIPT_PATH, $libraryName);
        return $this->filesystem->exists($path);
    }

    /**
     * @inheritDoc
     */
    public function getUpgradeScript($machineName, $majorVersion, $minorVersion)
    {
        $path = sprintf(ContentStorageSettings::UPGRADE_SCRIPT_PATH, $machineName);

        return $this->filesystem->exists($path) ? "/$path" : null;
    }

    public function saveFileFromZip($path, $file, $stream): bool
    {
        $filePath = Str::after($path, $this->uploadDisk->path("")) . "/" . $file;

        if (str_ends_with($filePath, '/')) {
            try {
                $this->uploadDisk->createDirectory($filePath);
                return true;
            } catch (FilesystemException $e) {
                $this->logger->error("Couldn't create directory while saving file from zip", [
                    'exception' => $e,
                ]);
                return false;
            }
        }

        $upload = $this->uploadDisk->put($filePath, $stream);

        if (preg_match('/^content\/(?:images|videos|audios|files)/', $file, $matches)) {
            $_tmpName = $path . "/" . $file;
            $fileInfo = pathinfo($_tmpName);
            $requestId = request()->header('X-Request-Id');
            H5PFile::create([
                'filename' => $fileInfo['basename'],
                'content_id' => null,
                'user_id' => \Session::get('authId'),
                'file_hash' => md5_file($_tmpName) ?? null,
                'external_reference' => $this->getFilePrefix(0) . Str::after($file, "content/") ?? null,
                'requestId' => $requestId,
            ]);
        }

        return $upload;
    }

    public function downloadContent(string $filename, string $title): RedirectResponse
    {
        $path = sprintf(ContentStorageSettings::EXPORT_PATH, $filename);

        return new RedirectResponse($this->filesystem->url($path));
    }

    public function getDisplayPath(bool $fullUrl = true)
    {
        return route('content.asset', null, $fullUrl);
    }

    public function getEditorDisplayPath(bool $fullUrl = true)
    {
        return route('content.asset', ['path' => ContentStorageSettings::EDITOR_PATH], $fullUrl);
    }

    public function getLibrariesPath()
    {
        return route('content.asset', ['path' => ContentStorageSettings::LIBRARY_DIR]);
    }

    public function getContentPath($id, $file)
    {
        return route('content.asset', ['path' => sprintf(ContentStorageSettings::CONTENT_PATH, $id) . $file]);
    }

    public function getAjaxPath()
    {
        return config('h5p.storage.publicPath');
    }

    public function alterLibraryFiles($files)
    {
        foreach ($files as $fileTypes) {
            foreach ($fileTypes as $file) {
                $path = Str::after($file->path, $this->getAjaxPath());
                $file->path = $path;
            }
        }

        return $files;
    }

    private function hasLibraryVersion($path, $versionString): bool
    {
        if (empty($versionString)) {
            return true;
        }
        return collect(explode("/", $path))
            ->filter(function ($element) {
                return \H5PCore::libraryFromString($element);
            })
            ->filter(function ($library) use ($versionString) {
                $libraryPath = sprintf(ContentStorageSettings::LIBRARY_JSONFILE_PATH, $library);
                if ($this->uploadDisk->has($libraryPath)) {
                    $libraryJsonContent = json_decode($this->uploadDisk->get($libraryPath));
                    return $versionString === sprintf(ContentStorageSettings::LIBRARY_VERSION_PREFIX, $libraryJsonContent->majorVersion, $libraryJsonContent->minorVersion, $libraryJsonContent->patchVersion);
                }
                return false;
            })
            ->isNotEmpty();
    }

    public function deleteLibrary($library)
    {
        $libraryPath = sprintf(ContentStorageSettings::LIBRARY_PATH, \H5PCore::libraryToFolderName($library));
        $deleteRemote = $this->deleteLibraryFromPath($libraryPath);
        $deleteLocal = $this->uploadDisk->exists($libraryPath) ? $this->uploadDisk->deleteDirectory($libraryPath) : true;

        return $deleteRemote && $deleteLocal;
    }

    public function storeContentOnDisk(string $filePath, $resource)
    {
        return $this->filesystem->put($filePath, $resource);
    }

    public function getFileUrl(string $path)
    {
        if ($this->filesystem->exists($path)) {
            return $this->filesystem->url($path);
        }

        return '';
    }
}
