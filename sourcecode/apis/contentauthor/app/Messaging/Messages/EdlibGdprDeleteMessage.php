<?php

namespace App\Messaging\Messages;

class EdlibGdprDeleteMessage
{
    public $userId;
    public $emails;

    public function __construct(array $data)
    {
        $this->userId = $data['userId'];
        $this->emails = $data['emails'] ?? [];
    }
}
