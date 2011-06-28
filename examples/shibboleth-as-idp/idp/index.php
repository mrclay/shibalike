<?php

// for the demo let's pretend Shibboleth works in this directory...
$_SERVER = array_merge($_SERVER, array (
  'UFADGroupsDN' => 'FakeGroup',
  'businessName' => 'User,Johnny B',
  'cn' => 'User, Johnny',
  'eduperson_affiliations' => '',
  'eppn' => 'juser@gmail.com',
  'givenName' => 'Johnny',
  'glid' => 'juser',
  'loa' => '2',
  'mail' => 'juser@gmail.com',
  'middleName' => 'B',
  'postalAddress' => '$$123 Fake St$GAINESVILLE$FL$326110001',
  'primary-affiliation' => 'T',
  'sn' => 'User',
  'uf_affiliations' => '',
  'ufid' => '32445260',
));


// the "IdP"
require dirname(__DIR__) . '/_inc.php';

$idp = new Shibalike\IdP(getStateManager(), getAttrStore(), getConfig());

if (isset($_GET['logout'])) {
    $idp->logout();
}

if ($idp->getValidAuthResult()) {
    header('Content-Type: text/html;charset=utf-8');
    echo "Already signed in as <b>" . htmlspecialchars($idp->getValidAuthResult()->getUsername(), ENT_QUOTES, 'UTF-8') . '</b>. <a href="?logout">Sign out</a>';
    die();
}

if (! isset($_SERVER['glid'])) {
    die('This directory is not protected by shibboleth.');
}

$username = $_SERVER['glid'];
$userAttrs = $idp->fetchAttrs($username);
if ($userAttrs) {
    $idp->markAsAuthenticated($username);
    $idp->redirect();
} else {
    // user is not in attr store!
    header('Content-Type: text/html;charset=utf-8');
    echo "Sorry. You're not in the attribute store. <a href='./'>Try again</a>";
    die();
}
