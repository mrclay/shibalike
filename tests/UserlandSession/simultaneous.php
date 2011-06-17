<?php
/**
 * Two Shibalike sessions and one native
 */

require __DIR__ . '/../../src/Shibalike/Util/UserlandSession/IStorage.php';
require __DIR__ . '/../../src/Shibalike/Util/UserlandSession/Storage/Files.php';
require __DIR__ . '/../../src/Shibalike/Util/UserlandSession.php';

use Shibalike\Util_UserlandSession as Sess;
use Shibalike\Util_UserlandSession_Storage_Files as Files;


$sess1 = new Sess(new Files());
$sess1->cache_limiter = Sess::CACHE_LIMITER_NONE;
$sess1->gc_divisor = 3;
$sess1->start();
$msg = "Sess1: Files stored in: " . $sess1->get_storage()->getPath();
$msg .= "<br>Sess1: ID: " . $sess1->id();
if (isset($sess1->data['i'])) {
    $sess1->data['i']++;
} else {
    $sess1->data['i'] = 0;
}
$msg .= "<br>Sess1: Counter: " . $sess1->data['i'];


session_start();
$msg .= "<br>Native session: ID: " . session_id();
if (isset($_SESSION['i'])) {
    $_SESSION['i']++;
} else {
    $_SESSION['i'] = 20;
}
$msg .= "<br>Native session: Counter: " . $_SESSION['i'];



$sess2 = new Sess(new Files());
$sess2->name('SHIBALIKE2');
$sess2->cache_limiter = Sess::CACHE_LIMITER_NONE;
$sess2->start();
$msg .= "<br>Sess2: Files stored in: " . $sess2->get_storage()->getPath();
$msg .= "<br>Sess2: ID: " . $sess2->id();
if (isset($sess2->data['i'])) {
    $sess2->data['i']++;
} else {
    $sess2->data['i'] = 10;
}
$msg .= "<br>Sess2: Counter: " . $sess2->data['i'];



$sess1->regenerate_id(true);
$msg .= "<br>Sess1: New session ID: " . $sess1->id();

session_regenerate_id(true);
$msg .= "<br>Native session: New session ID: " . session_id();

$sess2->regenerate_id(true);
$msg .= "<br>Sess2: New session ID: " . $sess2->id();


echo $msg;
