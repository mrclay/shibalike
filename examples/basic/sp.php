<?php
/**
 * Note, this script is just to emulate browser redirect flow in Shibboleth. You can use
 * the SP methods in any location (before headers are sent)
 */

// the "SP"
require '_inc.php';
$sp = new Shibalike\SP(getStateManager(), getConfig());

if (isset($_GET['sign-in'])) {
    $from = $_SERVER['HTTP_REFERER'];
    $sp->makeAuthRequest($_SERVER['HTTP_REFERER']);
    $sp->redirect();
} else {
    // sign-out
    $sp->logout();
    $sp->redirect('goodbye.php');
}

