<?php

require __DIR__ . '/../../vendor/autoload.php';

function getStateManager() {
	$session = \UserlandSession\SessionBuilder::instance()
		->setSavePath(sys_get_temp_dir())
		->setName('SHIBALIKE_BASIC')
		->build();
    return new \Shibalike\StateManager\UserlandSession($session);
}

// normally a DB
function getAttrStore() {
    return new \Shibalike\Attr\Store\ArrayStore(array(
        'jadmin' => array(
            'uid' => 1111,
            'displayname' => 'Johnny Admin',
        ),
        'juser' => array(
            'uid' => 2222,
            'displayname' => 'Jane User',
        ),
    ));
}

function getConfig() {
    $config = new \Shibalike\Config();
    $config->idpUrl = 'idp.php';
    return $config;
}