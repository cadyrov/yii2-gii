<?php

namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\base\Model;
use yii\web\UploadedFile;
use app\models\form\Upload;

class Files extends ActiveRecord{
	public function rules(){
        return [
            [['name', 'ext',], 'required','message'=>'Передайте параметр'],
            [['file_id', 'name', 'ext', 'owner_id', 'type_id' ,'add_date', 'user_id'], 'filter','filter'=>'trim'],
        ];
    }

}
?>
