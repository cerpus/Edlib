<?php

namespace App\Http\Controllers;

use App\AuthMigration;
use App\Http\Requests\CreateAuthMigrationRequest;
use Cerpus\LaravelRabbitMQPubSub\Facades\RabbitMQPubSub;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

final class AuthMigrationController extends Controller
{
    public function get(string $id): JsonResponse
    {
        $this->authorize('admin');

        /** @var AuthMigration $authMigrationData */
        $authMigrationData = Cache::get('auth-migration-' . $id);

        if ($authMigrationData == null) {
            throw new NotFoundHttpException("Auth migration was not found");
        }

        return new JsonResponse($authMigrationData);
    }

    public function create(CreateAuthMigrationRequest $request): JsonResponse
    {
        $this->authorize('admin');

        $authMigrationData = new AuthMigration($request->get('userIds'));

        Cache::put('auth-migration-' . $authMigrationData->id, $authMigrationData, now()->addDays(7));

        $userIds = [];
        foreach ($request->get('userIds') as $userId) {
            $userIds[] = $userId['from'];
        }

        RabbitMQPubSub::publish('auth_migration_get_info', json_encode([
            'id' => $authMigrationData->id,
            'userIds' => $userIds
        ]));

        return new JsonResponse($authMigrationData, Response::HTTP_CREATED);
    }

    public function execute(string $id): JsonResponse
    {
        $this->authorize('admin');

        /** @var AuthMigration $authMigrationData */
        $authMigrationData = Cache::get('auth-migration-' . $id);

        if ($authMigrationData == null) {
            throw new NotFoundHttpException("Auth migration was not found");
        }

        if (count($authMigrationData->tables) != 7) {
            throw new PreconditionFailedHttpException('migration hasnt gathered required data yet');
        }

        RabbitMQPubSub::publish('auth_migration_execute', json_encode([
            'id' => $authMigrationData->id,
            'userIds' => $authMigrationData->userIdToChange
        ]));

        return new JsonResponse($authMigrationData);
    }
}
