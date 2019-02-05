<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\datetime\DateTimePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
$cname = mb_strtolower(StringHelper::basename($generator->modelClass));
$this->title = $model-><?= $generator->getNameAttribute() ?>;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

            $form = ActiveForm::begin([
				'action' =>['/<?= $cname?>/upload/'],
				'options' => ['enctype' => 'multipart/form-data','class' => 'form-inline']
			]);
			echo Html::fileInput('Upload[file]', null);
            Html::submitButton(
				'',
				[
                    'class' => 'btn btn-success btn-xs glyphicon glyphicon-upload',
					'name' => 'add-button',
					'data-toggle' => 'tooltip',
				]
			);
            ActiveForm::end();

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
    }
}
?>
        ],
    ]) ?>

</div>
