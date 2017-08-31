yii-selectel
====================

Требования
-------------------
- php >= 5.4
- php-curl

Установка
-------------------
Распаковываем расширение в папку `extensions`.
В конфиг `main.php` в массив `components` прописывыем:
```
'components'=>[
    'selectel'=> [
        'class'=>'ext.yii-selectel.src.SelectelStorage',
        'user' => 70145,
        'key' => 'UqFXw1hi',
        'container' => '123'
    ],
],
```
Меняем  'user', 'key', 'container' на нужные, 
все параметры можно изменить в скрипте после инициализации расширения.
```php
$selectel = Yii::app()->selectel;
$selectel->setUser('name');
$selectel->setKey('key');
$selectel->setContainer('test');
```

Пример использования
-------------------
Пакетная загрузка файла(ов):
```php
$selectel = Yii::app()->selectel;
$selectel->uploadFiles([__DIR__ . '/../config/main.php', __DIR__ . '/../config/test.php']);
```        
Удаление файла(ов):

```php
$selectel = Yii::app()->selectel;
$selectel->deleteFiles(['main.php']);
```
Список файлов в контейнере:
```php
$selectel = Yii::app()->selectel;
$selectel->listFilesOnContainer();
```