yii2-gii alternative
==============


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist cadyrov/yii2-gii "*"
```

or add

```
"cadyrov/yii2-gii": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your web.php like this

```
'generators' => [
            'crud' => [
                'class' => 'cadyrov\gii\crud\Generator',
                'templates' => [
                    'crud' => 'cadyrov/gii/crud/default',
                ]
            ],
            'model' => [
                'class' => 'cadyrov\gii\model\Generator',
                'templates' => [
                    'model' => 'cadyrov/gii/model/default',
                ]
            ]
        ],
```
