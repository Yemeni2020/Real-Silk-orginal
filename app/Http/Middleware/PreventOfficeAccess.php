<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventOfficeAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // الحصول على المستخدم الحالي
        $user = auth('seller')->user();

        // التحقق مما إذا كان نوع الحساب "office"
        if ($user && $user->type_account === 'office') {
            abort(404);
        }

        return $next($request);
    }
}
