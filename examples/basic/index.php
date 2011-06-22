<?php

// the "SP"
require '_inc.php';

$sp = new Shibalike\SP(getStateManager(), getUrlConfig());
$_SERVER = $sp->mergeAttrs($_SERVER);
if (! $sp->userIsAuthenticated()) {
    $sp->redirect();
}

if (empty($_SERVER['REMOTE_USER'])) {
    die('Something went wrong. You should never see this.');
}


// your app's shibboleth auth module here

$username = $_SERVER['REMOTE_USER'];

// the "application"

header('Content-Type: text/html;charset=utf-8');

echo "<h1>Hello, " . htmlspecialchars($_SERVER['displayname'], ENT_QUOTES, 'UTF-8') . "!</h1>";

echo "<p><a href='idp.php?logout'>Sign out</a></p>";


