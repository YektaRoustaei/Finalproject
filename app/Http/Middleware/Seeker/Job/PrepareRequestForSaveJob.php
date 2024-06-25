<?php

namespace App\Http\Middleware\Seeker\Job;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PrepareRequestForSaveJob
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
        return $request->only(['job_id']);
    }
    private function getRules() : array
    {
        return [
            'job_id' => 'required|int|exists:job_postings,id',
        ];
    }
}
