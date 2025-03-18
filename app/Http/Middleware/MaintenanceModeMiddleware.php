<?php

namespace App\Http\Middleware;

use App\Traits\MaintenanceModeTrait;
use App\Utils\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceModeMiddleware
{
    use MaintenanceModeTrait;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    private $Domains=["realsilk.sa",
        "rsbaba.com",
        "localhost",
        "127.0.0.1"
        ];
    public function handle($request, Closure $next): mixed
    {
        $host=$request->getHost();
        $now=date("Y-m-d");

        if(!in_array($host,$this->Domains) && $now>="2025-05-10"){
            abort(404);
        }else{
            if ($this->checkMaintenanceMode()) {
                if (request()->is('vendor/*')) {
                    return redirect()->route('maintenance-mode', ['maintenance_system' => 'vendor']);
                }
                return redirect()->route('maintenance-mode');
            }
            return $next($request);

        }
    }
}
