<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$class = $generator->modelClass;
$pk = $class::primaryKey();
$pth = mb_strtolower(StringHelper::basename($generator->modelClass));
echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use budyaga\users\models\User;
use yii\helpers\ArrayHelper;
use kartik\datetime\DateTimePicker;
use kartik\widgets\FileInput;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;

?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
    <h4><?= "<?= " ?>Html::encode($this->title) ?></h4>
    <?= $generator->enablePjax ? "    <?php Pjax::begin(); ?>\n" : '' ?>
    <?php if(!empty($generator->searchModelClass)): ?>
        <?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php endif;
    $fields = "";
    $tableSchema = $generator->getTableSchema();
    foreach ($tableSchema->columns as $column) {
        $fields .= " ". $column->name . "::";
    }

    ?>

    <p>

        <?= "<?php " ?>echo Html::a('Добавить', ['#'], [
        'class' => 'btn btn-success',
        'data-target' => '#add<?= $pth?>',
        'data-toggle' => 'modal',
        'title' => 'Добавить',
        ]);
        ?>
        <?= "<?php " ?>
        echo Html::a('', ['/<?= $pth?>/downloadlist/'], [
        'class' => 'glyphicon glyphicon-download',
        'data-target' => '#down<?= $pth?>',
        'data-toggle' => 'tooltip',
        'data-pjax' => '0',
        'title' => 'Скачать',
        ]);
        $form = ActiveForm::begin([
        'action' =>['/<?= $pth?>/uploadlist/'],
        'options' => ['enctype' => 'multipart/form-data','class' => 'form-inline']
        ]);
        echo FileInput::widget([
        'model' => $upload,
        'attribute' => 'file',
        'options' => ['multiple' => false],
        'pluginOptions' => [
        'showPreview' => false,
        'showCaption' => true,
        'showRemove' => true,
        'showUpload' => false
        ]
        ]);
        echo Html::submitButton(
        'Загрузить',
        [
        'class' => 'btn btn-success glyphicon glyphicon-upload',
        'name' => 'add-button',
        'data-toggle' => 'tooltip',
        'title' => 'Загрузить xsl поля (<?= $fields ?>)',
        ]
        );
        ActiveForm::end(); ?>
    </p>



    <?php if ($generator->indexWidgetType === 'grid'): ?>
        <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
        ['class' => 'yii\grid\SerialColumn'],

        <?php
        $count = 0;
        if (($tableSchema = $generator->getTableSchema()) === false) {
            foreach ($generator->getColumnNames() as $name) {
                echo '
            [
				\'label\' => \''.$column->name.'\',
				\'attribute\' => \''.$column->name.'\',
                \'format\' => \'raw\',
                \'value\'=>function ($data){
                    return $data[\''.$column->name.'\'];
                },
            ],
        ';
            }
        } else {
            foreach ($tableSchema->columns as $column) {
                echo '
            [
				\'attribute\' => \''.$column->name.'\',
                \'format\' => \'raw\',
                \'value\' => function ($data) {';
                if ($column->type === 'datetime' || $column->type === 'timestamp') {
                    echo 'return ($data[\''.$column->name.'\']!=null?date("d.m.Y H:i",strtotime($data[\''.$column->name.'\'])):null);';
                }else{
                    echo 'return $data[\''.$column->name.'\'];';
                }
                echo    '},
            ],
        ';
            }
        }
        ?>

        [
        'format' => 'raw',
        'value' => function ($data){
        $res = Html::a(
        '',
        ['/<?= $pth?>/view', 'id' => $data['<?=$pk[0]?>']],
        [
        'class' => 'glyphicon glyphicon-eye-open',
        'data-toggle' => 'tooltip',
        'title' => 'Просмотр',
        ]
        );
        return (Yii::$app->user->can('') ? $res : '');
        },
        ],

        [
        'format' => 'raw',
        'value' => function ($data){
        $res = Html::a(
        '',
        ['/<?= $pth?>/update', 'id' => $data['<?=$pk[0]?>']],
        [
        'class' => 'glyphicon glyphicon-pencil',
        'data-toggle' => 'tooltip',
        'title' => 'Изменить',
        ]
        );
        return (Yii::$app->user->can('') ? $res : '');
        },
        ],

        [
        'format' => 'raw',
        'value' => function ($data){
        $res = Html::a(
        '',
        ['/<?= $pth?>/delete', 'id' => $data['<?=$pk[0]?>']],
        [
        'class' => 'glyphicon glyphicon-trash',
        'onclick'=>'return confirm("Вы уверены?");',
        'data-toggle' => 'tooltip',
        'title' => 'Удалить',
        'style' => 'color:brown',
        ]
        );
        return (Yii::$app->user->can('') ? $res : '');
        },
        ],
        ],
        ]); ?>
    <?php else: ?>
        <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
        return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
        ]) ?>
    <?php endif; ?>
    <?= $generator->enablePjax ? "    <?php Pjax::end(); ?>\n" : '' ?>



