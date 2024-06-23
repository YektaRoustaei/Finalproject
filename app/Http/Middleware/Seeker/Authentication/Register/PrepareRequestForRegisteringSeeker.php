<?php

namespace App\Http\Middleware\Seeker\Authentication\Register;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Closure;

class PrepareRequestForRegisteringSeeker
{
     /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next): mixed
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
    private function getData(Request $request) : array
    {
        return $request->only(['first_name' , 'last_name', 'email', 'address', 'phonenumber', 'password']);
    }
    private function getRules() : array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:seekers',
            'address' => 'required|string',
            'phonenumber' => 'required|string',
            'password' => 'required|string|min:8',
        ];
    }
}
