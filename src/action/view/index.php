<?php

use BoltSystem\Yii2Logs\src\action\drivers\LogDb;

$columns = [
    'id',
    'user_id',
    [
        'format'    => 'html',
        'attribute' => 'type_id',
        'value'     => function ($model) {
            return LogDb::getMapTypeIdToTypename()[$model->type_id];
        },
    ],
    [
        'format'    => 'html',
        'attribute' => 'action_id',
        'value'     => function ($model) {
            return LogDb::getMapActionIdToActionName()[$model->action_id];
        },
    ],
    [
        'format'    => 'html',
        'attribute' => 'entity_id',
        'value'     => function ($model) {
            return '<a href="/backend' . $model->getLinkEdit() . '" target="_blank">' . $model->getTypeName() . ' #' . $model->entity_id . '</a>';
        },
    ],
    'date_create',
    ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
];

$settings = [
    'columns'      => $columns,
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
];

$settings = array_merge($settings, LogDb::settingForIndex());
?>

<?= $this->render('@vendor/bolt-system/yii2-logs/src/base/views/base/index.php', $settings); ?>