<?php

namespace App\Http\Middleware;

use App\H5PContent;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use Closure;

class AdapterMode
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
        if( config('feature.allow-mode-switch') === true ){
            if ($request->isMethod('post') && in_array($request->get('adapterMode'), ['cerpus', 'ndla'])){
                $request->session()->put('adapterMode', $request->get('adapterMode'));
            } else {
                if( $request->routeIs('h5p.show') || $request->routeIs('h5p.edit')){
                    $id = $request->route('h5p');
                } elseif( $request->routeIs('h5p.ltishow') || $request->routeIs('h5p.ltiedit')){
                    $id = $request->route('id');
                }
                if( !empty($id)){
                    $content = H5PContent::findOrFail($id, ['content_create_mode', 'id']);
                    $request->session()->put('adapterMode', $content['content_create_mode']);
                }
                if( $request->session()->has('adapterMode')){
                    app(H5PAdapterInterface::class); // adapter is a singleton, run this to ensure that the adapter settings are applied
                }
            }
        }
        return $next($request);
    }
}
