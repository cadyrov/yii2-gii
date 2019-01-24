<?php
namespace cadyrov\gii;

use yii\db\ActiveRecord;
use yii\base\Model;

class Upload extends Model{

    public $file;

	public function rules()
    {
		return [
			[['file'], 'required', 'message' => 'Передайте файл'],
        ];
	}
}
