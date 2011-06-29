<?php

// for the demo let's pretend Shibboleth is protecting this directory...
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
    $idp->redirect('../goodbye.php');
}

// since shibboleth is protecting this directory, we know at this point,
// attributes will be present in $_SERVER.

$username = $_SERVER['glid'];
$userAttrs = $idp->fetchAttrs($username);
$idp->markAsAuthenticated($username);
$idp->redirect();
