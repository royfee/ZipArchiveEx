## ZipArchive 类的扩展
> 1、用法
>>  composer require royfee/ziparchiveex 进行安装

```php

$zip = new \royfee\zip\ZipArchiveEx;
$zip->open('my.zip');
//打包Oss里存储的图片
$file = [
    'http://aliyunoss.com/ade7.jpg',
];
$zip->addRemoteFiles($file);
$zip->close();
