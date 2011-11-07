<?php

function startup()
{
    // Настройки подключения к БД.
    $hostname = 'localhost'; 
    $username = 'root'; 
    $password = '';
    $dbName = 'fuskator';
    
    // Языковая настройка.
    setlocale(LC_ALL, 'ru_RU.CP1251');    
    
    // Подключение к БД.
    mysql_connect($hostname, $username, $password) or die('No connect with data base'); 
    mysql_query('SET NAMES cp1251');
    mysql_select_db($dbName) or die('No database');
}
    
    $BaseUrl = 'http://localhost/';
    define (_BaseUrl_,'http://localhost/');
    define(_GalleriesPerPage_,20);
    define(_sPathFullSize_,'/home/localhost/www/gallery/full/');
    $sPathFullSize = '/home/localhost/www/gallery/full/';
    define(_sPathThumbs150_, '/home/localhost/www/gallery/thumbs150/');
    $sPathThumbs150 = '/home/localhost/www/gallery/thumbs150/';
    define(_sPathThumbs800_, '/home/localhost/www/gallery/thumbs800/');
    $sPathThumbs800 = '/home/localhost/www/gallery/thumbs800/';
    
    // Открытие сессии.
    // session_start();
        
//}

?>
