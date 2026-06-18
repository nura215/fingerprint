<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = (string) config('services.device_api.token');
        $requestToken = (string) $request->header('X-DEVICE-TOKEN');

        if ($configuredToken === '' || ! hash_equals($configuredToken, $requestToken)) {
            return response()->json([
                'status' => 'rejected',
                'message' => 'Unauthorized device token.',
                'open_door' => false,
            ], 401);
        }

        return $next($request);
    }
}







