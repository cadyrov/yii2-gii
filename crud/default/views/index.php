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
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
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

		echo Html::a('', ['/<?= $pth?>/download/'], [
                        'class' => 'glyphicon glyphicon-download',
                        'data-target' => '#down<?= $pth?>',
						'data-toggle' => 'tooltip',
						'data-pjax' => '0',
						'title' => 'Скачать',
                    ]);

	    $form = ActiveForm::begin([
			'action' =>['/<?= $pth?>/upload/'],
			'options' => ['enctype' => 'multipart/form-data','class' => 'form-inline']
		]);
		echo $form->field($upload, 'file')->fileInput()->label('Файл');
        echo Html::submitButton(
		    'Загрузить',
			[
			    'class' => 'glyphicon glyphicon-upload',
				'name' => 'add-button',
				'style'=>'margin-top:10px;',
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
                \'format\' => \'raw\',
                \'value\'=>function ($data){';
                    if ($column->type === 'datetime') {
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
				'value'=>function ($data){
					return Html::a(
                        '',
                        ['/<?= $pth?>/view', 'id' => $data['<?=$pk[0]?>']],
                        [
						    'class' => 'glyphicon glyphicon-eye-open',
							'data-toggle' => 'tooltip',
							'title' => 'Просмотр',
						]
                    );
				},
			],

            [
				'format' => 'raw',
				'value'=>function ($data){
					return Html::a(
                        '',
                        ['/<?= $pth?>/update', 'id' => $data['<?=$pk[0]?>']],
                        [
						    'class' => 'glyphicon glyphicon-pencil',
							'data-toggle' => 'tooltip',
							'title' => 'Изменить',
						]
                    );
				},
			],

            [
				'format' => 'raw',
				'value'=>function ($data){
					return Html::a(
                        '',
                        ['/<?= $pth?>/delete', 'id' => $data['<?=$pk[0]?>']],
                        [
						    'class' => 'glyphicon glyphicon-trash',
							'onclick'=>'return confirm("Вы уверены?");',
							'data-toggle' => 'tooltip',
							'title' => 'Удалить',
						]
                    );
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



<div class="modal fade" id="add<?= $pth?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Добавить</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <?= "<?= " ?>$this->render('_form', [
                'model' => $model,
            ]) ?>
      </div>
    </div>
  </div>
</div>
</div>
