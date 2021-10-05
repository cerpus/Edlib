<?php


namespace App\Libraries\DataObjects;


class RCloneConfigObject
{

    public $user, $key, $authUrl, $region, $domain;

    public $RClone, $RCloneConfigPath, $remote, $container;

    public $checkers, $transfers;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->RClone = config('rclone.rclonePath');
        $this->RCloneConfigPath = config('rclone.rcloneConfigPath');
        $this->remote = config('rclone.remote');

        $this->user = config('services.openstack.username');
        $this->key = config('services.openstack.password');
        $this->authUrl = config('services.openstack.authUrl');
        $this->region = config('services.openstack.region');
        $this->domain = config('services.openstack.domain');
        $this->container = config('filesystems.disks.openstack.container');

        $this->checkers = config('rclone.checkers');
        $this->transfers = config('rclone.transfers');
    }
}
