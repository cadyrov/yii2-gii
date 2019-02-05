<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}
$tableSchema = $generator->getTableSchema();

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
$pth = mb_strtolower(StringHelper::basename($generator->modelClass));

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use cadyrov\gii\Upload;
use cadyrov\gii\File;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use yii\db\Query;
use yii\web\UploadedFile;

class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    const RES_TRUE = 10;
	const RES_FALSE = 20;
	const RES_NOONE = 30;

    private $error=[];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','view','create','delete','update','downloadlist','uploadlist','download','upload'],
                'rules' => [
                    [
                        'actions' => ['index','view','create','delete','update','download','upload'],
                        'allow' => true,
                        'roles' => ['<?= $pth?>'],
                    ],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
		$model = new <?= $modelClass ?>();
        $upload = new Upload();
<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'upload' => $upload,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'upload' => $upload,
        ]);
<?php endif; ?>
    }

    public function actionView(<?= $actionParams ?>)
    {
        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>),
        ]);
    }

    public function actionCreate()
    {
        $model = new <?= $modelClass ?>();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			<?php
			foreach ($tableSchema->columns as $column) {
				if ($column->type === 'datetime') {
					echo 'if ($model->'.$column->name.') {';
						echo '$model->'.$column->name.' = date("Y-m-d H:i:s",strtotime($model->'.$column->name.'));';
					echo '}';
				}
			}
			?>
            $model->save();
        }

        return $this->redirect(['index']);
    }

    public function actionUpdate(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			<?php
			foreach ($tableSchema->columns as $column) {
				if ($column->type === 'datetime') {
					echo 'if ($model->'.$column->name.') {';
						echo '$model->'.$column->name.' = date("Y-m-d H:i:s",strtotime($model->'.$column->name.'));';
					echo '}';
				}
			}
			?>
            $model->save();
			return $this->redirect(['index']);
        }
		return $this->render('update', [
            'model' => $model,
        ]);

    }

    public function actionDelete(<?= $actionParams ?>)
    {
        $this->findModel(<?= $actionParams ?>)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(<?= $generator->generateString('The requested page does not exist.') ?>);
    }

    public function actionDownloadlist()
    {
        $table = <?= $modelClass ?>::tableName();
		ob_end_clean();
		$query=new Query;
		$query->select('*')
		->from($table );
		$resarr=$query->all();

        <?php
            $columns = "[";
			$nonPrivateColumns = "[";
            $headers = "[";
            $tableSchema = $generator->getTableSchema();
            foreach ($tableSchema->columns as $column) {
                $columns .= '\'' . $column->name .'\', ';
                $headers .= '\'' . $column->name .'\' ' . " => " . '\'' . $column->name .'\', ';
				if ($column->name != $pks[0]) {
					$nonPrivateColumns .= '\'' . $column->name .'\', ';
				}

            }
			$columns .= "]";
			$nonPrivateColumns = "]";
            $headers .= "]";
        ?>

		return Excel::export([
            'format' => 'Xlsx',
			'asAttachment' => true,
            'fileName' => $table,
            'models' => $resarr,
            'columns' => <?= $columns?>,
            'headers' => <?= $headers?>,
        ]);
    }

    public function actionUploadlist()
    {
        set_time_limit(5000);
        $model = new Upload();
		$res="";
        if (Yii::$app->request->isPost) {
			$model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');

            $path = dirname(__DIR__).'/runtime/temp/';
            if (!file_exists($path) && !mkdir($path)) {
                return 'не удалось создать директорию';
            }
            if ($model->file && $model->validate()) {

                $fileName = 'upload_price_temp.xls';

                if (file_exists($path.$fileName)) {
                    unlink($path.$fileName);
                }
                $model->file->saveAs($path.$fileName);
                if (!file_exists($path.$fileName)) {
                    die('не удалось сохранить файл');
                }
				ob_end_clean();
                $data =Excel::import($path.$fileName,
                    ['setFirstRecordAsKeys' => true,
                    'setIndexSheetByName' => true,]);
                if (!is_array($data)) {
                    die('не удалось разобрать файл');
                }

                if (is_array($data) && count($data) > 0) {
                    foreach ($data as $n => $m) {
						if ($m != null && $this->issetParams($m) == self::RES_TRUE) {
							$res .= $this->updateRecord($m);
						} else {
							foreach($m as $k=>$v){
								$res .= $this->updateRecord($v);
							}
						}
					}
                } else {
					return print_r(serialize($data));
				}
            } else {
				return print_r(serialize($model->getErrors()));
			}
        } else {
			return 'is no post';
		}
        return $this->redirect(['index','message'=>serialize($res)]);
    }

    private function updateRecord($v){
		$res = "";
        if ($v != null && is_array($v)) {
            $isset = $this->issetParams($v);
            if ($isset == self::RES_TRUE) {
                $model = <?= $modelClass ?>::findOne($v['<?= $pks[0]?>']);
                if ($model == null) {
                    $model = new <?= $modelClass ?>();
                }
                $model->setAttributes($v);
                if ($model->validate()) {
					<?php
					foreach ($tableSchema->columns as $column) {
						if ($column->type === 'datetime') {
							echo 'if ($model->'.$column->name.') {';
								echo '$model->'.$column->name.' = date("Y-m-d H:i:s",strtotime($model->'.$column->name.'));';
							echo '}';
						}
					}
					?>
                    $model->save();
                } else {
                    return (serialize($model->getErrors()));
                }

            } elseif ($isset == self::RES_FALSE) {
                return ('Не все параметры переданы');
            }
        }
		return $res;
	}

	private function issetParams(array $array){
		$this->error = [];
		$nameArr = <?= $columns ?>;
		$result = self::RES_FALSE;
		$all = self::RES_FALSE;
		$one = self::RES_TRUE;
		foreach ($nameArr as $name) {
			if (!isset($array[$name])) {
				$this->error[] = $name;
				$one = self::RES_FALSE;
			} else {
				$all = self::RES_TRUE;
			}
		}

		if ($all == self::RES_FALSE) {
			$result = self::RES_NOONE;
		} elseif ($one == self::RES_FALSE) {
			$result = self::RES_FALSE;
		} else {
			$result = self::RES_TRUE;
		}

		return $result;
	}


	public function actionUpload()
	{
        $model = new Upload();
		$id = Yii::$app->request->post('<?= $pks[0]?>');
		$owner = <?=$modelClass?>::findOne($id);
        if (Yii::$app->request->isPost && $owner != null) {
			$model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
			if($model->validate()){
				$fl = new File();
				$fl->user_id = Yii::$app->user->identity->id;
				$fl->add_date = date ("Y-m-d H:i:s");
				$fl->owner_id = $owner-><?= $pks[0]?>;
				$fl->name = $model->file->name;
				$fl->ext = $model->file->extension;
				if ($fl->validate()) {
					$fl->save();
					if(!$fll->saveAs('path'.$fl->file_id)){
						$fl->delete();
					}
				}
			} else {
				//error code
            }
			return $this->redirect(['view', 'id' => '$owner-><?= $pks[0]?>');
        }
    }

	public function actionDownload()
	{
		if(Yii::$app->request->post('file_id')){
			$fl=File::findOne(Yii::$app->request->post('file_id'));
			if($fl!=null){
				$path='path'.$fl->file_id;
				if (file_exists($path)) {
					if (ob_get_level()) {
					  ob_end_clean();
					}
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename=' .$fl->name);
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');
					header('Content-Length: ' . filesize($path));
					readfile($path);
					exit;
				}else{
					print_r('Файла не существует в хранилище');
				}
			}else{
				print_r('Файл не найден в таблице');
			}
		}else{
			print_r('Не передан ид файла');
		}
	}
}
