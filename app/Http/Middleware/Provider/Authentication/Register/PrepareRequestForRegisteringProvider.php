<?php

namespace App\Http\Middleware\Provider\Authentication\Register;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PrepareRequestForRegisteringProvider
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validator = Validator::make($this->getData($request), $this->getRules());

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->getMessages() as $field => $message) {
                $errors[] = [
                    'field' => $field,
                    'message' => $message[0]
                ];
            }
            return response()->json(['errors' => $errors], 422);
        }

        return $next($request);
    }

    private function getData(Request $request): array
    {
        return $request->only([
            'company_name',
            'description',
            'telephone',
            'email',
            'password',
            'city_id'
        ]);
    }

    private function getRules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'telephone' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:providers',
            'password' => 'required|string|min:8',
            'city_id' => 'required|exists:cities,id', // Ensure city_id exists in the cities table
        ];
    }
}
