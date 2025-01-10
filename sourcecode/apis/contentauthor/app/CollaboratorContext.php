<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;

class CollaboratorContext extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    public static function contextShouldUpdate($systemId, $contextId, $timestamp): bool
    {
        if (!config('feature.context-collaboration', false)) {
            return false;
        }

        return self::where('system_id', $systemId)
            ->where('context_id', $contextId)
            ->where('timestamp', '>', Carbon::createFromTimestamp($timestamp))
            ->doesntExist();
    }

    public static function deleteContext($systemId, $contextId): void
    {
        if (!config('feature.context-collaboration', false)) {
            return;
        }

        self::where('system_id', $systemId)
            ->where('context_id', $contextId)
            ->delete();
    }

    public static function updateContext($systemId, $contextId, $collaborators, $resources, $timestamp): void
    {
        if (!config('feature.context-collaboration', false)) {
            return;
        }

        if (!self::contextShouldUpdate($systemId, $contextId, $timestamp)) {
            return;
        }
        DB::transaction(function () use ($systemId, $contextId, $collaborators, $resources, $timestamp) {
            self::deleteContext($systemId, $contextId);

            if (empty($collaborators) || empty($resources)) {
                return;
            }

            $data = [];
            foreach ($collaborators as $collaborator) {
                foreach ($resources as $resource) {
                    $item['system_id'] = $systemId;
                    $item['context_id'] = $contextId;
                    $item['type'] = $collaborator->type;
                    $item['collaborator_id'] = $collaborator->authId;
                    $item['content_id'] = $resource->contentAuthorId;
                    $item['timestamp'] = Carbon::createFromTimestamp((int) $timestamp);
                    $data[] = $item;
                }
            }

            if (!empty($data)) {
                self::insert($data);
            }
        });
    }

    /**
     * @throws Exception
     */
    public static function isUserCollaborator($collaboratorId, $resourceId): bool
    {
        if (!config('feature.context-collaboration', false)) {
            return false;
        }

        if (!$collaboratorId || !$resourceId) {
            return false;
        }

        return self::where('collaborator_id', $collaboratorId)
            ->where('content_id', $resourceId)
            ->exists();
    }

    public static function getResourceContextCollaborators($resourceId): array
    {
        if (!config('feature.context-collaboration', false) || !$resourceId) {
            return [];
        }

        return self::where('content_id', $resourceId)->get()->map(function ($collaborator) {
            return strtolower($collaborator->collaborator_id);
        })->toArray();
    }
}
