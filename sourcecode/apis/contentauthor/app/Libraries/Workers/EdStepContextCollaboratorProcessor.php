<?php

namespace App\Libraries\Workers;

use App\CollaboratorContext;

class EdStepContextCollaboratorProcessor
{
    protected $message = null;

    /**
     * EdStepContextCollaboratorProcessor constructor.
     * @param $messsage
     */
    public function __construct($message = null)
    {
        if ($message) {
            $this->setMessage($message);
        }
    }

    public function setMessage($message)
    {
        $this->validate($message);
        $this->message = $message;
    }

    public function process()
    {
        if (!$this->message) {
            throw new Exceptions\CottontailMissingMessageException('Message is empty. Nothing to process.');
        }

        CollaboratorContext::updateContext(
            $this->message->systemId,
            $this->message->contextId,
            $this->message->collaborators,
            $this->message->resources,
            $this->message->timestamp
        );
    }

    protected function validate($message)
    {
        if (!is_object($message)) {
            throw new Exceptions\CottontailBadMessageException("Message is not an object");
        }

        $fields = [
            'version', 'timestamp', 'type', 'systemId', 'courseId', 'contextId', 'collaborators', 'resources'
        ];

        foreach ($fields as $field) {
            if (!property_exists($message, $field)) {
                throw new Exceptions\CottontailBadMessageException("Message is missing field: $field");
            }
        }

        $collaboratorFields = ['type', 'authId'];
        foreach ($message->collaborators as $collaborator) {
            foreach ($collaboratorFields as $field) {
                if (!property_exists($collaborator, $field)) {
                    throw new Exceptions\CottontailBadMessageException("Collaborator is missing field: $field");
                }
            }
        }

        if ($message->type !== 'collaboratorChange') {
            throw new Exceptions\CottontailBadMessageException("Message is of wrong type. Expected collaboratorChange but got " . $message->type);
        }

        return true;
    }
}
