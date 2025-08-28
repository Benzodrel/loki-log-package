
<?php

use boltSystem\yii2Logs\src\error\drivers\ErrorLogDb;

$actions = ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'];

$columns = [
    'id',
    'level',
    'code',
    'description',
    [
        'label'  => "Доп. данные",
        'format' => 'html',
        'value'  => function ($model, $key, $index, $column)
        {
            $href = $model->url;
            $output  = '<p>URL: <a href="' . $href . '">' . $href . '</a></p>';
            $output .= '<p>Файл: ' . $model->file . '</p>';
            $output .= '<p>Строка: ' . $model->line . '</p>';
            $output .= '<p>Трассировка: ' . json_encode($model->trace, JSON_PRETTY_PRINT) . '</p>';
            return $output;
        },
        'contentOptions' => ['style' => 'min-width:250px; max-width:350px; word-wrap: break-word;']
    ],
    'date_create',

    $actions
];

$settings = [
    'columns'      => $columns,
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
];

$settings = array_merge($settings, ErrorLogDb::settingForIndex());

?>
<?=$this->render('@vendor/bolt-system/yii2-logs/src/base/views/base/index.php', $settings);