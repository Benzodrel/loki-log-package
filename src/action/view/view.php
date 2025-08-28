<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$class      = $model->getClassName();
$fields     = $model->getFields();
$attrTitle  = (new $class)->attributeLabels();

$this->title = $model->getTypeName() . ' #' . $model->entity_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$logData = $model->serializeLogData();

?>
<div class="log-view">

    <h1><?= '<a href="/backend' . $model->getLinkEdit() . '" target="_blank">' . $model->getTypeName() . ' #' . $model->entity_id . '</a>' ?></h1>

    <table class="table table-striped table-bordered detail-view">
        <tbody>
        <?php
        foreach( $logData as $name => $value )
        {
            echo "<tr>";

            echo "<th>" . $attrTitle[ $name ] . "</th><td>" . $value . "</td>";

            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

</div>