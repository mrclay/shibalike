<?php

require __DIR__ . '/../../src/Shibalike/Util/UserlandSession/IStorage.php';
require __DIR__ . '/../../src/Shibalike/Util/UserlandSession/Storage/Files.php';
require __DIR__ . '/../../src/Shibalike/Util/UserlandSession.php';

use Shibalike\Util_UserlandSession as Sess;
use Shibalike\Util_UserlandSession_Storage_Files as Files;

$storage = new Files();
$sess = new Sess($storage);
//$sess->cache_limiter = Sess::CACHE_LIMITER_PRIVATE_NO_EXPIRE;
$sess->gc_divisor = 3;
$sess->start();

if (isset($sess->data['i'])) {
    $sess->data['i']++;
} else {
    $sess->data['i'] = 0;
}

$sess->regenerate_id(true);
 
