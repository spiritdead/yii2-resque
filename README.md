# yii2-resque
Module yii2 resque

for use this library we need define in the configuration this parameters

(yii2-advanced)

common/config/main.php 
```php
'components' => [
    ...
    'yiiResque' => [
        'class' => 'spiritdead\yii2resque\components\YiiResque',
        'server' => 'localhost',
        'port' => '6379'
    ],
    ...
],
```

backend/config/main.php and console/config/main.php for use the commands
```php
'bootstrap' => [
    ...
    'resque',
    ...
],
'modules' => [
    ...
    'resque' => [
        'class' => 'spiritdead\yii2resque\Module',
        'layout' => '',
        'layoutAlias' => ''
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
                'spiritdead\yii2resque\migrations'
            ],
            //'migrationPath' => null, // allows to disable not namespaced migration completely
        ],
    ],
    ...
];
```