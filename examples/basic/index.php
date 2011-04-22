<?php

// the "SP"

if (empty($_SERVER['REMOTE_USER'])) {
    // shibboleth not working? fear not.

    // stuff here
}


// shibboleth auth module here

$username = $_SERVER['REMOTE_USER'];

// the "application"

header('Content-Type: text/html;shareset=utf-8');

echo "Hello, " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "!";
