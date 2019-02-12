<?php

if ($f3) {
    $f3->set('AUTOLOAD', 'app/');
    $f3->set('DEBUG', 3);
    $f3->set('LOCALES', 'app/dict/');
    $f3->set('LOGS', 'app/logs/');
    $f3->set('UI', 'app/views/');
    $f3->set('TEMP', 'tmp/');
    $f3->set('CACHE', 'folder=tmp/cache/');
    $f3->set('UPLOADS', 'uploads/');
    $f3->set('ESCAPE', false);
    
    $f3->set('PROTO', 'https');
    $f3->set('SITE', 'lindocarros.com');
    $f3->set('EMAIL_FROM', 'info@precisionq.com');    
       
    $f3->set('db_prefix', '');
    $f3->set('db_dsn','mysql:host=localhost;port=3306;dbname=lindocar_main');
    $f3->set('db_user', 'lindocar_main');
    $f3->set('db_password', '!jRAF_gKq3s9');

    $f3->set('layout','main');
    
    if (file_exists('config.ini')) {
        $f3->config('config.ini');
    }
}