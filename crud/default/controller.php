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
				'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page');
                },
                'only' => ['index','view','create','delete','update','downloadlist','uploadlist','download','upload'],
                'rules' => [
                    [
                        'actions' => ['index','view','create','delete','update','downloadlist','uploadlist','download','upload'],
                        'allow' => true,
                        'roles' => ['<?= $pth?>'],
                    ],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
		$dataQuery = <?= $modelClass ?>::find();
        $id = Yii::$app->request->get('id');
        if ($id) {
			$dataQuery->where(['id' => $id]);
		}
        self::ok($dataQuery->all());
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
        if ($model->load(Yii::$app->request->post())) {
			<?php
			foreach ($tableSchema->columns as $column) {
				if ($column->type === 'datetime') {
					echo 'if ($model->'.$column->name.') {';
						echo '$model->'.$column->name.' = date("Y-m-d H:i:s",strtotime($model->'.$column->name.'));';
					echo '}';
				}
			}
			?>
            if (!$model->save()) {
                self::error($model->getErrors());
                return;
            }
            self::ok($model);
            return;
        }
        self::error('data not send');
        return;
    }

    public function actionDelete()
	{
		$id = Yii::$app->request->post('id');
		if (!$id) {
			self::error('Send id');
			return;
		}
		$model = <?= $modelClass ?>::findOne($id);
		if (!$model) {
			self::error('<?= $modelClass ?> not found');
			return;
		}
		if ($model->delete()) {
			self::ok();
		} else {
            self::error($model->getErrors());
        }
	}


    public function actionUpdate(<?= $actionParams ?>)
    {
        
        $id = Yii::$app->request->post('id');
		if (!$id) {
			self::error('Send id');
			return;
		}
        $model = $this->findOne($id);
        if (!$model) {
			self::error('Model not found');
			return;
		}
        if ($model->account_id !== self::$user->account_id) {
			self::error('You can`t update this document');
			return;
		}
        $modelNew = new <?= $actionParams ?>();
        $modelNew->load(Yii::$app->request->post());
		<?php
		foreach ($tableSchema->columns as $column) {
			if ($column->type === 'datetime') {
                echo '$model->' . $column->name . ' = ($modelNew->'. $column->name . ' date("Y-m-d H:i:s", strtotime($model->'.$column->name.')) ? : null);';
			} else {
                echo '$model->' . $column->name . ' = $modelNew->'. $column->name . ';';
            }
		}
		?>
        if ($model->save()) {
            self::ok($model);
            return;
        }
        self::error($model->getErrors());
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
                    self::error('не удалось сохранить файл');
                    return
                }
				ob_end_clean();
                $data =Excel::import($path.$fileName,
                    ['setFirstRecordAsKeys' => true,
                    'setIndexSheetByName' => true,]);
                if (!is_array($data)) {
                    self::error('не удалось разобрать файл');
                    return
                }

                if (is_array($data) && count($data) > 0) {
					<?= $modelClass ?>::deleteAll();
                    foreach ($data as $n => $m) {
						if ($m != null && $this->issetParams($m) == self::RES_TRUE) {
							$res .= $this->updateRecord($m);
							if ($res != null) {
								break;
							}
						} else {
							foreach($m as $k=>$v){
								$res .= $this->updateRecord($v);
								if ($res != null) {
									break;
								}
							}
						}
					}
                } else {
					return self::error(serialize($data));
				}
            } else {
				return self::error(serialize($model->getErrors()));
			}
        } else {
			return 'is no post';
		}
        self::ok($res);
        return;
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
				if($v['<?= $pks[0]?>']){
					$model-><?= $pks[0]?> = $v['<?= $pks[0]?>'];
				} else {
					unset ($v['<?= $pks[0]?>']);
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
            return self::ok($owner);
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
					return self::error('Файла не существует в хранилище');
				}
			}else{
				return self::error('Файл не найден в таблице');
			}
		}else{
			return self::error('Не передан ид файла');
		}
	}
}
