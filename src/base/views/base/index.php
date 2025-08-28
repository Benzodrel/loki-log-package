<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title                   = $title;
$this->params['breadcrumbs'][] = $this->title;

if (!isset($emptyText)) {
    $emptyText = Yii::t('main', 'BASE_INDEX_EMPTYTEXT_Ничего не найдено');
}

?>
    <div class="index-view index-page">

        <h1>
            <?=Html::encode($this->title)?>
        </h1>

        <div class="gridview-wrapper">
            <?=GridView::widget([
                'dataProvider' => $dataProvider,
                'columns'      => $columns,
            ]);?>
        </div>
    </div>
<?php
?>