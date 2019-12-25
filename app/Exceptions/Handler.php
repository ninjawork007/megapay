<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        //if $exception is an HttpException
        if ($exception instanceof HttpException)
        {
            //get the status code
            $status = $exception->getStatusCode();
            if ($status == 403)
            {
                return response()->view('admin.errors.403', [], 403);
            }
            elseif ($status == 401)
            {
                return response()->view('admin.errors.401', [], 401);
            }
            elseif ($status == 503)
            {
                return response()->view('admin.errors.503', [], 503);
            }
        }

        // custom error message
        // if ($exception instanceof \ErrorException)
        // {
        //     return response()->view('admin.errors.500', [], 500);
        // }
        // else
        // {
        //     return parent::render($request, $exception);
        // }


        return parent::render($request, $exception);
    }
}
