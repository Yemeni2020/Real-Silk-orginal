<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\Helpers;
use Illuminate\Support\Facades\Auth;

class SellerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    
    
     public function handle($request, Closure $next)
    {
        if (auth('seller')->check()) {
            $user = auth('seller')->user();

            // ✅ التحقق من أن المستخدم موجود
            if (!$user) {
                auth()->guard('seller')->logout();
                return redirect()->route('vendor.auth.login');
            }

            // ✅ السماح بالدخول إذا كان الحساب "معتمد" والبريد الإلكتروني "مؤكد"
            $mustSignContract = getWebConfig('vendors_must_sing_contract') ?? 0;

            if (
                $user->status === 'approved' &&
                $user->verification && 
                (
                    ($mustSignContract && $user->signatures) || !$mustSignContract
                )
            ) {
                if ($request->route()->getName() !== 'vendor.dashboard.confirm_email') {
                    return $next($request);
                } else {
                    return redirect()->route('vendor.dashboard.index');
                }
            }


            // ✅ السماح بطلبات POST دون إعادة التوجيه لمنع فقدان CSRF
            if (!$user->verification  ) {
                
                if ($request->isMethod('post')) {
                    return $next($request); // السماح بطلبات POST بدون إعادة التوجيه
                }

                // ✅ السماح فقط بزيارة صفحة تأكيد البريد لمن لم يتم التحقق
                if ($request->route()->getName() !== 'vendor.dashboard.confirm_email') {
                    return redirect()->route('vendor.dashboard.confirm_email');
                }
                return $next($request);
            }
            else if(!$user->signatures && (getWebConfig('vendors_must_sing_contract') ?? 0)){

                // dump($request->route()->getName());
                // return redirect()->route('vendor.dashboard.signatures');
                if ($request->isMethod('post')) {
                    return $next($request); // السماح بطلبات POST بدون إعادة التوجيه
                }

                if ($request->route()->getName() !== 'vendor.dashboard.signatures') {
                    return redirect()->route('vendor.dashboard.signatures');
                }
                return $next($request);

            }

            // ✅ تسجيل الخروج إذا لم يكن الحساب معتمدًا
            auth()->guard('seller')->logout();
            return redirect()->route('vendor.auth.login');
        }

        // ✅ إعادة التوجيه لصفحة تسجيل الدخول إذا لم يكن المستخدم مسجلًا
        return redirect()->route('vendor.auth.login');
    }

     
    // public function handle($request, Closure $next)
    // {
    //     if (auth('seller')->check() && auth('seller')->user()->status == 'approved') {
    //             return $next($request);

            
    //     }
    //     auth()->guard('seller')->logout();

    //     return redirect()->route('vendor.auth.login');
    // }
}
