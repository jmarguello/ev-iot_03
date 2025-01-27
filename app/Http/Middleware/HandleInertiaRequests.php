<?php

namespace App\Http\Middleware;

use App\Models\Concentrador;
use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $ipAddress = $request->ip();
            $isAllowed = Concentrador::where('ip_address', $ipAddress)
                ->where('estado', 'activo')
                ->exists();

            if (!$isAllowed) {
                return response()->json(['error' => 'Concentrador no autorizado'], 403);
            }
        }

        return parent::handle($request, $next);
    }
}
