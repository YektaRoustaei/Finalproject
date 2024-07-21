<?php

namespace App\Http\Middleware\Seeker\Authentication\Login;

use App\Models\Seeker;
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
        $seeker = Seeker::query()->where('email',$request->email)->first();
        if (! $seeker || ! Hash::check($request->password, $seeker->password)) {
            throw ValidationException::withMessages([
                'email' => ['The seeker credentials are incorrect.'],
            ]);
        }
        request()->merge(['seeker' => $seeker]);
        return $next($request);
    }
}
