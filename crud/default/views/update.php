<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use kartik\datetime\DateTimePicker;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-update">

    <h1><?= '<?= ' ?>Html::encode($this->title) ?></h1>

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

    <?= '<?= ' ?>$this->render('_form', [
        'model' => $model,
        'form' => $form,
    ]) ?>

    <div class="form-group">
        <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Записать') ?>, ['class' => 'btn btn-success']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
