<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Session;
use App\ContentLock;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class UnlockController extends Controller
{
    /**
     * Unlocks a resource if user owns the lock
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        if (empty(config('feature.content-locking'))) {
            abort(Response::HTTP_NOT_FOUND);
        }
        if (!Session::get('authId', false)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $status = 'OK';
        $code = Response::HTTP_OK;

        // Don't care if the lock is expired or not
        $lock = ContentLock::where("content_id", $id)->get()->first();

        if (!$lock) {
            $status = 'not found';
            $code = Response::HTTP_OK; // This is ok, Lock may have expired on its own or been deleted manually.
        } else {
            if ($lock->auth_id == Session::get('authId')) {
                $lock->delete();
            } else {
                $status = 'fail';
                $code = Response::HTTP_FORBIDDEN;
            }
        }

        return response()->json([
            'status' => $status,
            'code' => (int) $code,
        ], $code);
    }
}
