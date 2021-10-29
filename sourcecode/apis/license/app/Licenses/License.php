<?php

namespace App\Licenses;

class License {
    // Permissions
    const RETAIN = 'retain'; // the right to make, own, and control copies of the content (e.g., download, duplicate, store, and manage)
    const REUSE = 'reuse'; // the right to use the content in a wide range of ways (e.g., in a class, in a study group, on a website, in a video)
    const REVISE = 'revise'; // the right to adapt, adjust, modify, or alter the content itself (e.g., translate the content into another language)
    const REMIX = 'remix'; // the right to combine the original or revised content with other open content to create something new (e.g., incorporate the content into a mashup)
    const REDISTRIBUTE = 'redistribute'; // the right to share copies of the original content, your revisions, or your remixes with others (e.g., give a copy of the content to a friend)
    const COMERCIAL = 'commercial'; // Allow commercial use
    const SUBLICENSE = 'sublicense';

    static $availablePermissions = [
        self::RETAIN,
        self::REUSE,
        self::REVISE,
        self::REMIX,
        self::REDISTRIBUTE,
        self::COMERCIAL,
        self::SUBLICENSE
    ];

    private $id;
    private $name;
    private $permissions;

    public function __construct(string $id, string $name, array $permissions)
    {
        $this->id = $id;
        $this->name = $name;
        $this->permissions = $permissions;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPermissions() : array
    {
        return $this->permissions;
    }
}