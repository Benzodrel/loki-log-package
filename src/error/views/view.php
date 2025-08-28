<?php
use BoltSystem\Yii2Logs\log\error\drivers\ErrorLogDb;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model BoltSystem\Yii2Logs\log\error\drivers\ErrorLogDb */

$this->title                   = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Logs Error', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-error-view">

    <h1><?= $model->id?></h1>

    <p>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data'  => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method'  => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model'      => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Уровень',
                'value' => ErrorLogDb::getLevelsArray()[$model->level],
            ],
            'code',
            'description:ntext',
            [
                'label'  => 'URL',
                'format' => 'html',
                'value'  => '<a href="' . $model->url . '">' . $model->url . '</a>',
            ],
            [
                'label' => 'Файл',
                'value' => $model->file,
            ],
            [
                'label' => 'Строка',
                'value' => $model->line,
            ],
            [
                'label'  => 'Трассировка',
                'format' => 'html',
                'value'  => '<pre>' . json_encode($model->trace, JSON_PRETTY_PRINT) . '</pre>',
            ],
            [
                'label' => 'Пользователь',
                'value' => $model->user_id ?: '(' . Yii::t('main', 'GRIDVIEW_DROPDOWN_STATUS_Неизвестно') . ')',
            ],
            'date_create',
            [
                'label'  => 'Данные запроса',
                'format' => 'html',
                'value'  => '<pre>' . json_encode($model->request_data, JSON_PRETTY_PRINT) . '</pre>',
            ],
        ],
    ]) ?>

</div>