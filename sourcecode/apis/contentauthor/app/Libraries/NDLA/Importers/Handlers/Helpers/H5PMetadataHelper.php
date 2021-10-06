<?php


namespace App\Libraries\NDLA\Importers\Handlers\Helpers;


use Carbon\Carbon;

trait H5PMetadataHelper
{
    static $H5P_COLLABORATOR_ROLE_AUTHOR = 'Author';
    static $H5P_COLLABORATOR_ROLE_EDITOR = 'Editor';
    static $H5P_COLLABORATOR_ROLE_LICENSEE = 'Licensee';
    static $H5P_COLLABORATOR_ROLE_ORIGINATOR = 'Originator';

    protected $h5pDateFormat = 'd-m-y H:i:s';

    public function setImageLicense($licenseString)
    {
        $h5pStyleLicenseString = $this->toH5PLicenseString($licenseString);
        if ($h5pStyleLicenseString) {
            $this->content->params->file->copyright->license = $h5pStyleLicenseString;
            $this->content->params->file->copyright->version = '4.0';
        } else {
            $this->content->params->file->copyright->license = 'U';
        }

        $this->setMetaLicense($licenseString);

        return $this;
    }

    public function setMetaLicense($licenseString)
    {
        $edLibLicenseString = $this->toEdLibLicenseString($licenseString);
        if ($edLibLicenseString) {
            $this->license = $edLibLicenseString;
        }

        if ($h5pStyleLicenseString = $this->toH5PLicenseString($licenseString)) {
            $this->content->metadata->license = $h5pStyleLicenseString;
            $this->content->metadata->licenseVersion = '4.0';
        } else {
            $this->content->metadata->license = 'U';
            $this->content->metadata->licenseVersion = '4.0';
        }

        return $this;
    }

    public function addMetaComment($comment)
    {
        $this->content->metadata->authorComments = $comment;
    }

    public function addMetaLicenseExtras($extras)
    {
        $this->content->metadata->licenseExtras = $extras;
    }

    public function addMetaChange($author, $log, Carbon $date = null)
    {
        // {"date":"08-05-19 10:26:06","author":"Odd-Arne Johansen","log":"Changed something"}
        if (!$date) {
            $date = now();
        }

        $change = (object)[
            'date' => $date->format($this->h5pDateFormat),
            'author' => $author,
            'log' => $log,
        ];

        if ($author && $log) {
            $this->content->metadata->changes[] = $change;
        }

        return $this;
    }

    public function addMetaCollaborator($name, $role, $readonly = true)
    {
        //  {"name":"Odd-Arne Johansen","role":"Author","readonly":false}
        $role = $this->squashRole($role);

        if ($name && $role) {
            $this->content->metadata->authors[] = (object)[
                'name' => $name,
                'role' => $role,
                'readonly' => $readonly
            ];
        }
        return $this;
    }

    public function addMetaAuthor($name, $readonly = true)
    {
        return $this->addMetaCollaborator($name, self::$H5P_COLLABORATOR_ROLE_AUTHOR, $readonly);
    }

    public function addMetaEditor($name, $readonly = true)
    {
        return $this->addMetaCollaborator($name, self::$H5P_COLLABORATOR_ROLE_EDITOR, $readonly);
    }

    public function addMetaLicensee($name, $readonly = true)
    {
        return $this->addMetaCollaborator($name, self::$H5P_COLLABORATOR_ROLE_LICENSEE, $readonly);
    }

    public function addMetaOriginator($name, $readonly = true)
    {
        return $this->addMetaCollaborator($name, self::$H5P_COLLABORATOR_ROLE_ORIGINATOR, $readonly);
    }


    protected function squashRole($role)
    {
        $role = strtolower($role);

        $rolesMap = [
            'author' => self::$H5P_COLLABORATOR_ROLE_AUTHOR,
            'editor' => self::$H5P_COLLABORATOR_ROLE_EDITOR,
            'licensee' => self::$H5P_COLLABORATOR_ROLE_LICENSEE,
            'originator' => self::$H5P_COLLABORATOR_ROLE_ORIGINATOR,
            'photographer' => self::$H5P_COLLABORATOR_ROLE_AUTHOR,
            'supplier' => self::$H5P_COLLABORATOR_ROLE_ORIGINATOR,
        ];

        if (!array_key_exists($role, $rolesMap)) {
            return null;
        }

        return $rolesMap[$role];
    }
}
