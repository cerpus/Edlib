<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use App\Game;

class GameAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $game = Game::findOrFail($request->id);
        if($game->isOwner(Session::get('authId', false))
        || $game->isCollaborator()
        || $game->isCopyable()
        || $game->isExternalCollaborator(Session::get('authId', false))){
            return $next($request);
        }

        Log::error(__METHOD__ . ': Access denied. Game: ' . $this->request->h5p
            . ' is not owned or shared with user:' . Session::get('authId', 'not-logged-in-user'));
        Log::debug(['user' => Session::get('userId', 'not-logged-in-user'), 'url' => request()->url(), 'request' => request()->all()]);

        abort(403, 'Access denied, you are not the owner of the game or it is not shared with you.');
    }
}
