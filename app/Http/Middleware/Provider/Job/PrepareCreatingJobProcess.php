<?php

namespace App\Http\Middleware\Provider\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Closure;
class PrepareCreatingJobProcess
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
        return $request->only(['title', 'description', 'salary', 'type', 'location', 'category_ids']);
    }
    private function getRules() : array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|string',
            'type' => 'required|string',
            'location' => 'nullable|string',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id', // Ensure each ID exists in the categories table
        ];
    }
}
