<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
use yii\helpers\ArrayHelper;

class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{

    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->db !== 'db'): ?>

    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    public function rules()
    {
        return [<?= empty($rules) ? '' : ("\n            " . implode(",\n            ", $rules) . ",\n        ") ?>];
    }

    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>

    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>

/**
    const TYPE_A;
    const TYPE_B;

    public static function getTypeArray()
    {
        return [
            self::TYPE_A,
            self::TYPE_B,
        ];
    }

    public static function getTypeMap()
    {
        $map = [
            ['id' => self::TYPE_A, 'name' => 'A'],
            ['id' => self::TYPE_B, 'name' => 'B'],
        ];
        return $map;
    }

    public static function getTipeById($id)
    {
        return ArrayHelper::getValue($id,self::getTypeMap());
    }

    public static function getXXXMap($id)
    {
        return ArrayHelper::map(self::query(), 'id', 'name');
    }

    //rule custom
    ['type', 'in', 'range' => self::getTypeArray()],
    [['a', 'b'], 'required', 'when' => function ($data) {
        if ($data->a == null && $data->b == null && $data->tnved == null) {
            return true;
        }
        return false;
    }, 'whenClient' => "function (attribute, value) {
        return $('#a').val() == '' && $('#b').val() == '';
    }", 'message' => 'Необходимо заполнить хотя бы одно из полей a, b],

*/





}
