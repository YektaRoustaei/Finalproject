<?php

namespace App\Http\Middleware\Seeker\Authentication\Register;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PrepareRequestForRegisteringSeeker
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

    /**
     * Get the data to be validated from the request.
     *
     * @param Request $request
     * @return array
     */
    private function getData(Request $request): array
    {
        return $request->only([
            'first_name',
            'last_name',
            'email',
            'phonenumber',
            'password',
            'password_confirmation', // Added
            'city_id'
        ]);
    }

    /**
     * Get the validation rules for the request data.
     *
     * @return array
     */
    private function getRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:seekers',
            'phonenumber' => 'required|string|max:15',
            'password' => 'required|string|min:8',
            'city_id' => 'required|exists:cities,id',
        ];
    }
}
