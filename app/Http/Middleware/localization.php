<?php

namespace App\Http\Middleware;

use Closure;

class localization
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
        // default api language ar

        $request->headers->set('Accept', 'application/json');
        $lang = $request->header('x-local')??'ar';
        if($lang){
            app()->setLocale($lang);

        }

        return $next($request);
    }
}
