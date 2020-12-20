<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Exceptions\Auth\HashNotDecodeException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        InvalidRequestException::class,
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
        if ($exception instanceof ValidationException) {
            if ($request->expectsJson()) {
                $errors = $exception->validator->errors()->first();
                return response()->json([
                    'status' => 'error',
                    'message' => $errors,
                    'code' => 422
                ]);
            } else {
            }
        }

        if ($exception instanceof ThrottleRequestsException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => '操作频繁，请稍后再试',
                    'code' => 429
                ]);
            }
        }

        if($exception instanceof MethodNotAllowedHttpException){
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => '接口请求格式不正确',
                'data' => (object)array()
            ]);
        }

        if($exception instanceof AuthorizationException){
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => Code::AUTH_NOT_COMPANY_MEMBER_MESSAGE,
                'data' => (object)array()
            ]);
        }

        if($exception instanceof ModelNotFoundException){
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => '未找到数据',
                'data' => (object)array()
            ]);
        }

        if($exception instanceof HashNotDecodeException){
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'ID不合法',
                'data' => (object)array()
            ]);
        }

        if($exception instanceof UnauthorizedHttpException){
            switch ($exception->getMessage())
            {
                case 'Could not decode token: Error while decoding to JSON: Malformed UTF-8 characters, possibly incorrectly encoded':
                    return response()->json([
                        'status' => 'error',
                        'message' => '登录已过期',
                        'code' => 403,
                        'data' => (object)array()
                    ]);
                case 'Token has expired':
                    return response()->json([
                        'status' => 'error',
                        'message' => '登录已过期',
                        'code' => 403,
                        'data' => (object)array()
                    ]);
                case 'The token has been blacklisted':
                    return response()->json([
                        'status' => 'error',
                        'message' => '登录已过期',
                        'code' => 403,
                        'data' => (object)array()
                    ]);
                case 'Token not provided':
                    return response()->json([
                        'status' => 'error',
                        'message' => '请先登录',
                        'code' => 401,
                        'data' => (object)array()
                    ]);
                default:
                    return response()->json([
                        'status' => 'error',
                        'message' => '请先登录',
                        'code' => 401,
                        'data' => (object)array()
                    ]);
            }
        }

        if($exception instanceof HttpException){
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => '无权请求'
            ]);
        }

        return parent::render($request, $exception);
    }
}
