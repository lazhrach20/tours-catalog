<?php

use App\Http\Middleware\RequestIdMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use App\Exceptions\CategoryInUseException;
use App\Exceptions\LlmUnavailableException;
use App\Exceptions\PublishTourException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->prependToGroup('api', RequestIdMiddleware::class);
        $middleware->prependToGroup('api', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/v1/admin/auth/login',
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                throw new AuthenticationException('Unauthenticated.');
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (SymfonyResponse $response, \Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return $response;
            }

            $requestId = $request->attributes->get('request_id');
            if (is_string($requestId) && $requestId !== '' && ! $response->headers->has('X-Request-Id')) {
                $response->headers->set('X-Request-Id', $requestId);
            }

            return $response;
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'Unauthenticated.',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        });

        $exceptions->render(function (LlmUnavailableException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage(),
                'code' => $exception->codeKey ?? 'LLM_UNAVAILABLE',
            ], 503);
        });

        $exceptions->render(function (PublishTourException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors,
                'code' => 'TOUR_PUBLISH_INVALID',
            ], 422);
        });

        $exceptions->render(function (CategoryInUseException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 'CATEGORY_IN_USE',
            ], 409);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'Forbidden.',
                'code' => 'FORBIDDEN',
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'Resource not found.',
                'code' => 'NOT_FOUND',
            ], 404);
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof NotFoundHttpException) {
                return null;
            }

            $payload = [
                'message' => $exception->getMessage() ?: 'Request failed.',
            ];

            $code = $exception->getHeaders()['X-Error-Code'] ?? null;
            if (is_string($code) && $code !== '') {
                $payload['code'] = $code;
            }

            return response()->json($payload, $exception->getStatusCode());
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof ValidationException
                || $exception instanceof AuthenticationException
                || $exception instanceof AuthorizationException
                || $exception instanceof CategoryInUseException
                || $exception instanceof LlmUnavailableException
                || $exception instanceof PublishTourException
                || $exception instanceof HttpException) {
                return null;
            }

            report($exception);

            return response()->json([
                'message' => 'Server error.',
                'code' => 'SERVER_ERROR',
            ], 500);
        });
    })->create();
