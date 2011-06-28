<?php

// the "SP"
require '_inc.php';
$sp = new Shibalike\SP(getStateManager(), getConfig());
$sp->initLazySession();



// the "application"

// _SERVER vars may not exist!
$name = empty($_SERVER['displayname'])
    ? 'Anonymous'
    : $_SERVER['displayname'];

header('Content-Type: text/html;charset=utf-8');

echo "<h1>Hello, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!</h1>";

echo "<p>This is a non-protected resource with a \"lazy\" session. Access the <a href='protected.php'>protected resource</a>.</p>";

echo "<p><a href='sign-in.php'>Sign in</a> | <a href='idp.php?logout'>Sign out</a></p>";
