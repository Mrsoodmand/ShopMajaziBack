<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Next.js storefront API: errors use the same envelope as App\Http\Traits\ApiResponse
        $this->renderable(function (Throwable $e, $request) {
            if (!$request->is('api/v1/*')) {
                return null;
            }

            $errors = null;
            if ($e instanceof ValidationException) {
                $status = $e->status;
                $message = $e->getMessage();
                $errors = $e->errors();
            } elseif ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                $status = 404;
                $message = 'Not found';
            } elseif ($e instanceof AuthenticationException) {
                $status = 401;
                $message = 'Unauthenticated';
            } elseif ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: (Response::$statusTexts[$status] ?? 'Error');
            } else {
                $status = 500;
                $message = config('app.debug') ? $e->getMessage() : 'Server error';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], $status);
        });
    }
}
