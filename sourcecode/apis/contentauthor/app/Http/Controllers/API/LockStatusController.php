<?php

namespace App\Http\Controllers\API;

use App\Content;
use App\ContentLock;
use App\Libraries\DataObjects\ContentLockDataObject;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use function config;

class LockStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($id)
    {
        $content = Content::findContentById($id);
        $userId = Session::get('authId', false);
        if (empty(config('feature.content-locking')) || empty($content)) {
            abort(404);
        }
        if (!$userId) {
            abort(403);
        }

        /** @var ContentLock $lock */
        $lock = ContentLock::notExpiredById($id);
        $lockData = ContentLockDataObject::create([
            'isLocked' => (bool) $lock,
        ]);

        if (!$lockData->isLocked) {
            $currentEditUrl = $content->getEditUrl();
            $latestEditUrl = $content->getEditUrl(true);
            if ($currentEditUrl === $latestEditUrl) {
                (new ContentLock())->lock($id);
            }
            $lockData->editUrl = $latestEditUrl;
        }

        return response()->json($lockData->toArray());
    }

    public function pulse($id)
    {
        $userId = Session::get('authId', false);
        if (config('feature.content-locking') !== true || empty($userId)) {
            abort(404);
        }

        /** @var ContentLock $lock */
        $lock = ContentLock::notExpiredById($id);
        if ($lock && $lock->auth_id === $userId && $lock->created_at->addHours(config('feature.lock-max-hours'))->isFuture()) {
            $lock->touch();
        }
    }
}
