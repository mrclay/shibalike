<?php

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

