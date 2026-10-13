<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class LogoutInactivePhysicalStoreUser
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if (
                $user &&
                isset($user->user_type) &&
                $user->user_type === 'physical_store_user' &&
                (int) $user->is_active !== 1
            ) {
                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => __('admin.account_inactive_logout'),
                    ], 401);
                }

                return redirect()
                    ->route('web.user.login')
                    ->with('error', __('admin.account_inactive_logout'));
            }
        }

        return $next($request);
    }
}
