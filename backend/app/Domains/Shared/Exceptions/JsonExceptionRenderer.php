<?php

namespace App\Domains\Shared\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * The single implementation of the standard JSON error envelope (API_DOCUMENTATION.md).
 */
class JsonExceptionRenderer
{
    public static function appliesTo(Request $request): bool
    {
        return $request->is('api/*', 'admin/api/*', 'webhooks/*') || $request->expectsJson();
    }

    public function __invoke(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! self::appliesTo($request) || $exception instanceof HttpResponseException) {
            return null;
        }

        return match (true) {
            $exception instanceof ValidationException => $this->respond(422, 'validation_failed', 'The given data was invalid.', $exception->errors()),
            $exception instanceof AuthenticationException => $this->respond(401, 'unauthenticated', 'Unauthenticated.'),
            $exception instanceof ApplicationException => $this->respond($exception->status, $exception->errorCode, $exception->getMessage(), $exception->errors),
            $exception instanceof HttpExceptionInterface => $this->respondToHttpException($exception),
            default => $this->respond(500, 'server_error', 'Something went wrong. Please try again.'),
        };
    }

    private function respondToHttpException(HttpExceptionInterface $exception): JsonResponse
    {
        $status = $exception->getStatusCode();

        [$code, $message] = match ($status) {
            401 => ['unauthenticated', 'Unauthenticated.'],
            403 => ['forbidden', 'This action is unauthorized.'],
            404 => ['not_found', 'The requested resource was not found.'],
            405 => ['method_not_allowed', 'The request method is not allowed.'],
            409 => ['conflict', 'The request conflicts with the current state.'],
            419 => ['csrf_expired', 'Your session has expired. Please refresh and try again.'],
            429 => ['rate_limited', 'Too many requests. Please try again later.'],
            503 => ['maintenance', 'The service is temporarily unavailable.'],
            default => $status >= 500
                ? ['server_error', 'Something went wrong. Please try again.']
                : ['request_error', 'The request could not be processed.'],
        };

        return $this->respond($status, $code, $message, [], $exception->getHeaders());
    }

    /**
     * @param  array<string, list<string>>  $errors
     * @param  array<string, mixed>  $headers
     */
    private function respond(int $status, string $code, string $message, array $errors = [], array $headers = []): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message, 'code' => $code];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        $payload['request_id'] = Context::get('request_id') ?? (string) Str::ulid();

        return new JsonResponse($payload, $status, $headers);
    }
}
