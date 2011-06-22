<?php
/**
 * Two Shibalike sessions and one native
 */

require __DIR__ . '/../../src/Shibalike/Util/UserlandSession/IStorage.php';
require __DIR__ . '/../../src/Shibalike/Util/UserlandSession/Storage/Files.php';
require __DIR__ . '/../../src/Shibalike/Util/UserlandSession.php';
require __DIR__ . '/../../src/Shibalike/Util/IdGenerator.php';

use Shibalike\Util\UserlandSession;
use Shibalike\Util\UserlandSession\Storage\Files;

$msgs = array();

$sess1 = UserlandSession::factory();
$sess1->cache_limiter = UserlandSession::CACHE_LIMITER_NONE;
$sess1->gc_divisor = 3;
$sess1->start();

$msgs['sess1']['name'] = $sess1->get_storage()->getName();
$msgs['sess1']['path'] = $sess1->get_storage()->getPath();
$msgs['sess1']['id'] = $sess1->id();
if (isset($sess1->data['i'])) {
    $sess1->data['i']++;
} else {
    $sess1->data['i'] = 0;
}
$msgs['sess1']['counter'] = $sess1->data['i'];


session_start();
$msgs['native']['name'] = session_name();
$msgs['native']['path'] = session_save_path();
$msgs['native']['id'] = session_id();
if (isset($_SESSION['i'])) {
    $_SESSION['i']++;
} else {
    $_SESSION['i'] = 20;
}
$msgs['native']['counter'] = $_SESSION['i'];


$sess2 = UserlandSession::factory();
$sess2->cache_limiter = UserlandSession::CACHE_LIMITER_NONE;
$sess2->start();
$msgs['sess2']['name'] = $sess2->get_storage()->getName();
$msgs['sess2']['path'] = $sess2->get_storage()->getPath();
$msgs['sess2']['id'] = $sess2->id();
if (isset($sess2->data['i'])) {
    $sess2->data['i']++;
} else {
    $sess2->data['i'] = 10;
}
$msgs['sess2']['counter'] = $sess2->data['i'];


$sess1->regenerate_id(true);
$msgs['sess1']['new_id'] = $sess1->id();

session_regenerate_id(true);
$msgs['native']['new_id'] = session_id();

$sess2->regenerate_id(true);
$msgs['sess2']['new_id'] = $sess2->id();

header('Content-Type: text/plain');
echo "Note three simultaneous sessions, including a native one.\n";
echo "All counters increment independently and IDs are regenerated on every request.\n\n";
var_export($msgs);
