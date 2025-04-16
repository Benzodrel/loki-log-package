<?php
use app\models\ErrorLog;
use app\models\helpers\BackendView;

$actions = BackendView::GridViewColumn_Actions('{view}', [
]);

$columns = [
    BackendView::GridViewColumn_ID($searchModel),
    BackendView::GridViewColumn_ErrorLogLevels($searchModel),
    'code',
    [
        'attribute'      => 'description',
        'contentOptions' => ['style' => 'min-width:250px; max-width:350px; word-wrap: break-word;'],
    ],
    [
        'attribute'      => "url",
        'format'         => 'html',
        'value'          => function ($model, $key, $index, $column) {
            return '<a href="' . $model->url . '">' . $model->url . '</a>';
        },
        'contentOptions' => ['style' => 'min-width:250px; max-width:350px; word-wrap: break-word;'],
    ],
    [
        'label'          => "Доп. данные",
        'format'         => 'html',
        'value'          => function ($model, $key, $index, $column) {
            $output = '<p>Файл: ' . $model->file . '</p>';
            $output .= '<p>Строка: ' . $model->line . '</p>';
            return $output;
        },
        'contentOptions' => ['style' => 'min-width:250px; max-width:350px; word-wrap: break-word;'],
    ],
    BackendView::GridViewColumn_User($searchModel, 'user_id'),
    BackendView::GridViewColumn_DateRange($searchModel, 'date_create'),

    $actions,
];

$settings = [
    'columns'      => $columns,
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
];

$settings = array_merge($settings, ErrorLog::settingForIndex());

?>
<?=$this->render('../base/index.php', $settings);