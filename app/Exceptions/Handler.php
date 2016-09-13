<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->wantsJson()) {
            $response = [
                'message' => (string) $e->getMessage(),
                'status' => 400,
            ];

            if ($e instanceof HttpException) {
                $response['message'] = Response::$statusTexts[$e->getStatusCode()];
                $response['status'] = $e->getStatusCode();
            }

            if ($e instanceof ValidationException) {
                return parent::render($request, $e);
            }

            if ($e instanceof ModelNotFoundException) {
                $response['message'] = Response::$statusTexts[Response::HTTP_NOT_FOUND];
                $response['status'] = Response::HTTP_NOT_FOUND;
            }

            if ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                $response['message'] = 'token_exception';
                $response['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                $response['message'] = 'token_expired';
                $response['status'] = Response::HTTP_UNAUTHORIZED;
            }

            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                $response['message'] = 'token_invalid';
                $response['status'] = Response::HTTP_UNAUTHORIZED;
            }

            if ($e instanceof \Ramsey\Uuid\Exception\UnsatisfiedDependencyException) {
                $response['message'] = $e->getMessage();
                $response['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
                $response['message'] = $e->getMessage();
                $response['status'] = $e->getStatusCode();
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
                $response['message'] = $e->getMessage();
                $response['status'] = $e->getStatusCode();
            }

            if ($this->isDebugMode()) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'trace' => $e->getTrace(),
                ];
            }

            return response()->json(['error' => $response], $response['status']);
        }

        return parent::render($request, $e);
    }

    public function isDebugMode()
    {
        return (bool) env('APP_DEBUG');
    }
}
