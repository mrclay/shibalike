<?php

// the "SP"
require '_inc.php';
$sp = new Shibalike\SP(getStateManager(), getConfig());
$sp->requireValidUser();



// your app's shibboleth auth module here

$username = $_SERVER['glid'];


// the "application"

header('Content-Type: text/html;charset=utf-8');

echo "<h1>Hello, " . htmlspecialchars($_SERVER['businessName'], ENT_QUOTES, 'UTF-8') . "!</h1>";

echo "<p><a href='idp/?logout'>Sign out</a></p>";
