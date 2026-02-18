<?php

namespace App\Libraries\H5P\Traits;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Exception;
use App\H5PFile;
use App\Libraries\H5P\Interfaces\H5PFileInterface;
use Carbon\Carbon;

/**
 * @method release($delay = 0)
 */
trait FileUploadTrait
{
    protected $contentId;
    protected $h5pFileId;

    /** @var Filesystem */
    protected $filesystem;

    public $timeout = 120;

    protected function processFile(H5PFile $file)
    {
        if ($this->postponeExecution($file)) {
            return;
        }
        $file->process_start = Carbon::now();
        $file->save();
        $this->filesystem = resolve('H5PFilesystem');
        if (!empty($file->params)) {
            [
                'from' => $fromPath,
                'to' => $toPath,
                'action' => $fileAction,
            ] = (array) $file->params;
            if (!empty($fromPath) && !empty($toPath) && $this->filesystem->exists($fromPath) && $this->filesystem->missing($toPath)) {
                $result = $this->performAction($fromPath, $toPath, $fileAction);
                if (!$result) {
                    $file->state = H5PFile::FILE_FAILED;
                    $file->save();
                    Log::error("Could not handle copy/move file '$fromPath' to '$toPath'. Boolean false returned...");
                    throw new Exception("Couldn't copy file");
                }
                $file->state = H5PFile::FILE_READY;
            } else {
                Log::warning("File exists $fromPath: " . $this->filesystem->exists($fromPath));
                Log::warning("Missing $toPath: " . $this->filesystem->missing($toPath));
            }
        } else {
            Log::warning("Missing params. Somethings rotten in the state of Denmark");
            $file->state = H5PFile::FILE_FAILED;
        }
        $file->save();
    }

    private function performAction($from, $to, $fileAction)
    {
        if ($fileAction === H5PFileInterface::ACTION_COPY) {
            return $this->filesystem->copy($from, $to);
        } elseif ($fileAction === H5PFileInterface::ACTION_MOVE) {
            return $this->filesystem->move($from, $to);
        }
    }

    private function postponeExecution(H5PFile $file): bool
    {
        if (!is_null($file->process_start)) {
            $now = Carbon::now();
            $nextStart = Carbon::parse($file->process_start)->addMinute();
            if ($nextStart->isAfter($now)) {
                $diffInSeconds = $nextStart->diffInRealSeconds($now);
                $this->release(min(60, $diffInSeconds));
                return true;
            }
        }
        return false;
    }
}
