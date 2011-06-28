<?php

// the "SP"
require '_inc.php';
$sp = new Shibalike\SP(getStateManager(), getConfig());

$from = $_SERVER['HTTP_REFERER'];

$sp->makeAuthRequest($_SERVER['HTTP_REFERER']);
$sp->redirect();