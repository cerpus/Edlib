<?php

namespace App\Http\Middleware;

use App\Content;
use App\H5PContent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Closure;

class CheckOwnership
{
    private $request;
    private $content;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;
        $contentId = $this->getContentId();
        if (!empty($contentId)) {
            /** @var Content $content */
            $content = Content::findContentById($contentId);
            if ($content->isOwner($this->getUserId()) || $content->isCollaborator() || $content->isCopyable() || $content->isExternalCollaborator($this->getUserId())) {
                return $next($request);
            }
        }
        Log::error(
            __METHOD__ . ': Access denied. H5P: ' . $this->request->h5p
            . ' is not owned or shared with user:' . Session::get('authId', 'not-logged-in-user'),
            [
                'user' => Session::get('userId', 'not-logged-in-user'),
                'url' => request()->url(),
                'request' => request()->all()
            ]
        );
        abort(403, 'Access denied, you are not the owner of the resource or it is not shared with you.');
    }

    /**
     * Get userId from session
     * @return int userId;
     * TODO: Return real userId;
     */

    private function getUserId()
    {
        $userId = Session::get('authId', false);

        return $userId;
    }

    /**
     * Get contentId of request
     * @return int contentId
     */
    private function getContentId()
    {
        $contentId = $this->request->h5p; //Due to automatic resource generation -> id is called h5p for some reason. Use "$php artisan route:list" to view route variables
        if ($contentId instanceof H5PContent) {
            return $contentId->id;
        } elseif ($contentId === null) {
            return $this->request->id;
        }

        return (int)$contentId;
    }
}
