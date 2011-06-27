<?php

require __DIR__ . '/../autoload.php';

$storage = new Shibalike\Util\UserlandSession\Storage\Pdo('SHIBALIKE', array(
    'dsn' => "mysql:host=localhost;dbname=shibalike;charset=UTF-8",
    'username' => 'user_shibalike',
    'password' => 'hello',
));

$sess = new Shibalike\Util\UserlandSession($storage);

$sess->start();

if (isset($sess->data['i'])) {
    $sess->data['i']++;
} else {
    $sess->data['i'] = 0;
}

if (! isset($sess->data['mbstring'])) {
    $sess->data['mbstring'] = "āēīōūĀĒĪŌŪ ānd sōmētīmēs ȳȲ";
}

header('Content-Type: text/html;charset=utf-8');
echo $sess->data['i'] . '<br>' . $sess->data['mbstring'];