<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response
    {
        logger()->error($e->getMessage(), [
            'exception' => $e,
        ]);

        if ($request->expectsJson()) {
            return apiResponse(JsonResponse::HTTP_INTERNAL_SERVER_ERROR , __('messages.error_occurred'), ['error' => $e->getMessage()]);
        }

        return parent::render($request, $e);
    }
}
