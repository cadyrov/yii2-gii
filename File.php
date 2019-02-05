<?php
namespace cadyrov\gii;

use Yii;
use yii\base\Model;

class Files extends ActiveRecord{
	public function rules(){
        return [
            [['name', 'ext',], 'required','message'=>'Передайте параметр'],
            [['file_id', 'name', 'ext', 'owner_id', 'type_id' ,'add_date', 'user_id'], 'filter','filter'=>'trim'],
        ];
    }

}
?>
