<?php
/**
 * This demonstrates the emulation of shibboleth protection. If there's a valid shibalike
 * session, the $_SERVER attributes will be available. If not, program flow will end
 * during requireValidUser() and the user will be redirected to the idpUrl in the config.
 */

require '_inc.php';
$sp = new Shibalike\SP(getStateManager(), getConfig());
$sp->requireValidUser();

if ($sp->username != 'jadmin') {
    die('Only jadmin may see this resource. <a href="sp.php?sign-in">Sign in</a> as him.');
}

// your app's shibboleth auth module here

$username = $_SERVER['REMOTE_USER'];


// the "application"

header('Content-Type: text/html;charset=utf-8');

echo "<h1>Hello, " . htmlspecialchars($_SERVER['displayname'], ENT_QUOTES, 'UTF-8') . "!</h1>";

echo "<p>This is a protected resource.</p>";

echo "<p><a href='sp.php?logout'>Sign out</a></p>";
