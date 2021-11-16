<?php


namespace App\Libraries\H5P\Helper;


use App\Libraries\DataObjects\RCloneConfigObject;
use App\Libraries\DataObjects\ContentStorageSettings;
use Exception;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Support\Facades\Storage;

class RClone
{
    private $RCloneConfig, $disk;

    public function __construct(RCloneConfigObject $configObject)
    {
        $this->RCloneConfig = $configObject;
        $this->disk = Storage::disk(config('h5p.H5PStorageDisk'));
    }

    /**
     * Sets the remote.
     *
     * @param string $remote
     *
     * @return bool
     */
    private function validateRemote($remote)
    {
        return !empty($remote) && in_array($remote, $this->listRemotes());
    }

    public function handleLibraryCopy()
    {
        if (!$this->isCloudStorageActive()) {
            return true;
        }
        $this->createConfigFile();

        if (!$this->validateRemote($this->RCloneConfig->remote)) {
            throw new Exception("Invalid remote set in config");
        }

        return $this->copyLibraries();
    }

    private function isCloudStorageActive()
    {
        return config('app.useContentCloudStorage') === true;
    }

    private function listRemotes()
    {
        $listremotes = $this->do('listremotes');
        return $listremotes[0];
    }

    private function createConfigFile()
    {
        if (Storage::exists($this->RCloneConfig->RCloneConfigPath)) {
            return;
        }

        $configName = Str::before($this->RCloneConfig->remote, ":");
        $configData = <<<RCLONE_CONFIG
[$configName]
type = swift
user = {$this->RCloneConfig->user}
key = {$this->RCloneConfig->key}
auth = {$this->RCloneConfig->authUrl}
domain = {$this->RCloneConfig->domain}
tenant =
tenant_domain =
region = {$this->RCloneConfig->region}
storage_url =
auth_version =

RCLONE_CONFIG;
        $configFile = Storage::put($this->RCloneConfig->RCloneConfigPath, $configData);
        if (!$configFile) {
            throw new Exception("Config not written");
        }
    }

    private function getRemoteLibraryPath()
    {
        return $this->RCloneConfig->remote . $this->RCloneConfig->container . "/" . ContentStorageSettings::LIBRARY_DIR;
    }

    /**
     * @throws Exception
     */
    private function copyLibraries()
    {
        $command = sprintf("copy %s %s %s", $this->getOptions(), $this->getRemoteLibraryPath(), $this->disk->path(ContentStorageSettings::LIBRARY_DIR));
        $result = $this->do($command);
        if (!empty($result[0])) {
            throw new Exception(sprintf("Copying failed with output: %s", $result[0]));
        }
        if ($result[1] !== 0) {
            throw new Exception(sprintf("Copying exited with return value: %s", $result[1]));
        }
        return true;
    }

    /**
     * @param string $command
     *
     * @return array
     * @throws RuntimeException
     *
     */
    private function do($command)
    {
        exec($this->RCloneConfig->RClone . ' --config ' . Storage::path($this->RCloneConfig->RCloneConfigPath) . ' ' . escapeshellcmd($command), $output, $returnValue);

        return [$output, $returnValue];
    }

    /**
     * @return string
     */
    private function getOptions()
    {
        $options = collect(['-v']);

        $options->when(!is_null($this->RCloneConfig->checkers), function ($collection) {
            return $collection->push('--checkers=' . $this->RCloneConfig->checkers);
        });

        $options->when(!is_null($this->RCloneConfig->transfers), function ($collection) {
            return $collection->push('--transfers=' . $this->RCloneConfig->transfers);
        });

        return $options->map(function ($option) {
            return escapeshellarg($option);
        })
            ->join(" ");
    }
}
