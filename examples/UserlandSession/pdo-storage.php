<?php

require __DIR__ . '/../autoload.php';

$storage = new Shibalike\Util\UserlandSession\Storage\Pdo('SHIBALIKE', array(
    'dsn' => "mysql:host=localhost;dbname=shibalike",
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

echo $sess->data['i'];