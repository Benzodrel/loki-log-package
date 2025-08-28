<?php


namespace boltSystem\yii2Logs\src\error\handlers;

use Yii;
use yii\base\ErrorException;
use yii\base\ExitException;
use yii\base\UserException;
use yii\console\ErrorHandler as BaseHandler;
use yii\console\Exception;
use yii\console\UnknownCommandException;
use yii\helpers\Console;
use yii\helpers\Url;

class ErrorHandlerConsole extends BaseHandler
{
    public $error_types;

    /**
     * @var boolean Used to specify display_errors php ini setting
     */
    public $display_errors = false;


    protected function _handleError($code, $message, $errID)
    {
            $name = Yii::t('yii', 'Error') . ' (#' . $code . ')';

            echo "Error #{$errID}: {$name}\n";
            echo $message . "\n";

            exit();
    }

    public function handleFatalError()
    {
        $e = error_get_last();

        if (is_null($e)) {
            return;
        }

        $code = $e['type'];

        $message = $e['message'];
        $level   = Yii::$app->errorLog::mapPHPLevel($e['type']);
        $meta    = ['file' => $e['file'], 'line' => $e['line']];

        $errID = Yii::$app->errorLog::RegisterError($code, $message, $level, $meta);

        if (!$errID) {
            return;
        }

        $this->_handleError($code, Yii::t('app', 'Fatal error #{error_id}', ['error_id' => $errID]), $errID);
    }

    public function handleError($code, $message, $file, $line)
    {
        if (!(error_reporting() & $code)) {
            return;
        }

        try {
            $e = error_get_last();

            if (is_null($e)) {
                return;
            }

            $type = Yii::$app->errorLog::mapPHPLevel($e['type']);

            $errId = Yii::$app->errorLog::RegisterError($code, $message, $type, ['file' => $file, 'line' => $line]);

            if ($e['type'] == Yii::$app->errorLog::LEVEL_WARNING || $e['type'] == Yii::$app->errorLog::LEVEL_NOTICE) {
                return;
            }

            $this->_handleError($code, $message, $errId);

        } catch (\Throwable$e) {
            echo 'Error when error: ' . $e->getMessage();

            exit();
        }
    }

    public function handleException($exception)
    {
        if ($exception instanceof ExitException) {
            return;
        }

        try {
            $errId = Yii::$app->errorLog::RegisterErrorByErrorException($exception);

            $code = Yii::$app->errorLog::GetProtectedProperty($exception, 'statusCode');

            $this->_handleError($code, $exception->getMessage(), $errId);

        } catch (\Exception$e) {
            // an other exception could be thrown while displaying the exception
            $this->handleFallbackExceptionMessage($e, $exception);
        } catch (\Throwable$e) {
            // additional check for \Throwable introduced in PHP 7
            $this->handleFallbackExceptionMessage($e, $exception);
        }
    }
}