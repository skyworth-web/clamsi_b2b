<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        });
    }
    public function render($request, Throwable $exception)
    {


        if ($exception instanceof AuthenticationException) {
            if (!$request->expectsJson() && (Str::contains($_SERVER['REQUEST_URI'], '/admin') || Str::contains($_SERVER['REQUEST_URI'], '/seller/') || Str::contains($_SERVER['REQUEST_URI'], '/delivery_boy/'))) {
                return redirect()->route('admin.login');
            } else {

                return redirect()->route('login');
            }
        }

        if ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof HttpException) {
            return $this->renderHttpException($exception);
        }
        return parent::render($request, $exception);
    }
}
