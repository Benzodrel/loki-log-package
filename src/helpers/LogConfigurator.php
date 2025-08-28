<?php

namespace boltSystem\yii2Logs\src\src\helpers;

class LogConfigurator
{
    public static function buildLogConfig(array $config, array $components, array $params): array
    {
        foreach ($components as $component) {
            switch ($component) {
                case 'errorLog':
                    $config['bootstrap'][] = 'errorLog';
                    $config['components']['errorLog'] = $params['components']['errorLog'];
                    $config['components']['errorHandler'] = $params['components']['errorHandler'];
                    break;

                case 'Log':
                    $config['bootstrap'][] = 'Log';
                    $config['components']['Log'] = $params['components']['Log'];
                    break;

                case 'profiler':
                    $config['bootstrap'][] = 'profiler';
                    $config['components']['profiler'] = $params['components']['profiler'];
                    break;

                case 'eventLog':
                    $config['bootstrap'][] = 'eventLog';
                    $config['components']['eventLog'] = $params['components']['eventLog'];
                    break;
            }
        }
        if (isset($params['log']) && $params['log'] === 'loki') {
            $config['bootstrap'][] = 'log';
            $config['components']['log'] = $params['components']['log'];
        }

        return $config;
    }
}