<?php
namespace boltSystem\yii2Logs\src\target;

use yii\helpers\ArrayHelper;
use yii\log\Logger;

class CustomLokiLogTarget extends \cebe\lokilogtarget\LokiLogTarget
{
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        $level = $this->remapLevel($level, $category);
        if ($level === false) {
            return false;
        }

        if ($this->contextLevels === null || in_array($level, $this->contextLevels, true)) {
            $context = $this->getContextMessageArray();
            $text = array_merge($text , $context);
        }

        $labels = $this->labels;
        if ($this->levelLabel) {
            $labels[$this->levelLabel] = $level;
            $labels['code'] = $text['code']??'-';
            $labels['target'] = ArrayHelper::remove($text, 'log_type', '-');
        }

        $lokiMessage = json_encode($text);
        return [
            'stream' => $labels,
            'values' => [
                [(string)$this->getNanoTime($timestamp), $lokiMessage]
            ]
        ];
    }

    protected function getContextMessageArray():array
    {
        $context = ArrayHelper::filter($GLOBALS, $this->logVars);
        foreach ($this->maskVars as $var) {
            if (ArrayHelper::getValue($context, $var) !== null) {
                ArrayHelper::setValue($context, $var, '***');
            }
        }

        return $context;
    }
}