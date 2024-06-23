<?php

namespace App\Http\Middleware\Provider\Authentication\Login;

use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CheckCredential
{
        /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $provider = Provider::query()->where('email',$request->email)->first();
        if (! $provider || ! Hash::check($request->password, $provider->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        request()->merge(['provider' => $provider]);
        return $next($request);
    }
}
