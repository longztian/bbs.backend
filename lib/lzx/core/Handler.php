<?php declare(strict_types=1);

namespace lzx\core;

use ErrorException;
use Exception;
use Throwable;
use lzx\core\Logger;

class Handler
{
    private static $errorHandler;
    private static $exceptionHandler;
    public static $logger;
    public static $displayError = true;

    public static function setErrorHandler()
    {
        if (!isset(self::$errorHandler)) {
            $handler = [__CLASS__, 'errorHandler'];
            if (is_callable($handler)) {
                set_error_handler($handler, error_reporting());
                self::$errorHandler = $handler;
            } else {
                throw new Exception('failed to set error handler');
            }
        }
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function setExceptionHandler()
    {
        if (!isset(self::$exceptionHandler)) {
            $handler = [__CLASS__, 'exceptionHandler'];
            if (is_callable($handler)) {
                set_exception_handler($handler);
                self::$exceptionHandler = $handler;
            } else {
                throw new Exception('failed to set exception handler');
            }
        }
    }

    public static function exceptionHandler(Throwable $e)
    {
        if (self::$logger instanceof Logger) {
            self::$logger->error($e->getMessage(), $e->getTrace());
            self::$logger->flush();
        } else {
            error_log($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        if (self::$displayError) {
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
        }
    }
}
