<?php
namespace AbdAlrzaq\Roles\Middleware;

use Closure;

class CheckRole
{
    public function handle($request, Closure $next, $role)
    {
        if (!auth()->user() || !auth()->user()->roles->contains('name', $role)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
