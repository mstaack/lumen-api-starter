<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @param  \Exception $e
     * @return void
     * @throws Exception
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //Validation
        if ($e instanceof ValidationException) {
            return $e->getResponse();
        }

        // AuthorizationException
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'errors' => [
                'title' => 'Unauthorized'
                ]
            ], 401);
        }

        //Http Exceptions
        if ($e instanceof HttpException) {
            $response['message'] = $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()];
            $response['status']  = $e->getStatusCode();

            return response()->json($response, $response['status']);
        }

        //Default Exception Response
        $response = [
            'message' => 'Whoops! Something went wrong.',
            'status'  => 500
        ];

        if ($this->isDebugMode()) {
            $response['debug'] = [
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'code'      => $e->getCode(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ];

            //clean trace
            foreach ($e->getTrace() as $item) {
                if (isset($item['args']) && is_array($item['args'])) {
                    $item['args'] = $this->cleanTraceArgs($item['args']);
                }
                $response['debug']['trace'][] = $item;
            }
        }

        return response()->json($response, $response['status']);
    }
    /**
     * Determine if the application is in debug mode.
     *
     * @return Boolean
     */
    public function isDebugMode()
    {
        return (boolean)env('APP_DEBUG');
    }
    /**
     * @param array $args
     * @param int   $level
     * @param int   $count
     *
     * @return array|string
     */
    private function cleanTraceArgs(array $args, $level = 0, &$count = 0)
    {
        $result = [];
        foreach ($args as $key => $value) {
            if (++$count > 1e4) {
                return '*SKIPPED over 10000 entries*';
            }
            if (is_object($value)) {
                $result[$key] = get_class($value);
            } elseif (is_array($value)) {
                if ($level > 10) {
                    $result[$key] = '*DEEP NESTED ARRAY*';
                } else {
                    $result[$key] = $this->cleanTraceArgs($value, $level + 1, $count);
                }
            } elseif (is_null($value)) {
                $result[$key] = null;
            } elseif (is_bool($value)) {
                $result[$key] = $value;
            } elseif (is_int($value)) {
                $result[$key] = $value;
            } elseif (is_resource($value)) {
                $result[$key] = get_resource_type($value);
            } elseif ($value instanceof \__PHP_Incomplete_Class) {
                $array        = new \ArrayObject($value);
                $result[$key] = $array['__PHP_Incomplete_Class_Name'];
            } elseif (is_string($value) && mb_detect_encoding($value) === false) {
                $result[$key] = 'REMOVED-BINARY-BLOB';
            } else {
                $result[$key] = (string)$value;
            }
        }
        return $result;
    }
}
