<?php

namespace App\Http\Middleware\Provider\Authentication\Register;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrepareRequestForRegisteringProvider
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
        return $request->only(['company_name' , 'description', 'address', 'telephone', 'email', 'password']);
    }
    private function getRules() : array
    {
        return [
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'telephone' => 'required|integer',
            'email' => 'required|string|email|max:255|unique:providers',
            'password' => 'required|string|min:8',
        ];
    }
}
