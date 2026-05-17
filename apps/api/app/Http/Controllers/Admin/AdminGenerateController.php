<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateTourAction;
use App\Exceptions\LlmUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateTourRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AdminGenerateController extends Controller
{
    public function __construct(
        private GenerateTourAction $generateTourAction,
    ) {}

    public function store(GenerateTourRequest $request): JsonResponse
    {
        $key = 'tour-generate:'.($request->user()?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many generation requests. Try again later.',
                'code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, 3600);

        try {
            $draft = $this->generateTourAction->execute($request->validated());
        } catch (LlmUnavailableException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->codeKey ?? 'LLM_UNAVAILABLE',
            ], 503);
        }

        return response()->json([
            'message' => 'Tour draft generated successfully.',
            'data' => $draft,
        ]);
    }
}
