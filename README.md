# yii2-resque
Module yii2 resque

for use this library we need define in the configuration this parameters

common/config/main.php (yii2-advanced)
```php
'modules' => [
    ...
    'resque' => [
        'class' => 'spiritdead\resque\Module',
    ],
    ...
],
'components' => [
    ...
    'yiiResque' => [
        'class' => 'spiritdead\resque\components\YiiResque',
        'server' => 'localhost',
        'port' => '6379'
    ],
    ...
],
```
for activate the migrations add this in the console/main.php
```php
return [
    ...
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => [
                'spiritdead\resque\migrations'
            ],
            //'migrationPath' => null, // allows to disable not namespaced migration completely
        ],
    ],
    ...
];
```