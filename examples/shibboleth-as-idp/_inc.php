<?php

require __DIR__ . '/../../vendor/autoload.php';

function getStateManager() {
	$session = \UserlandSession\SessionBuilder::instance()
		->setSavePath(sys_get_temp_dir())
		->setName('SHIBALIKE_SHIBIDP')
		->build();
    return new \Shibalike\StateManager\UserlandSession($session);
}

// get attributes from Shibboleth!
function getAttrStore() {
    $source = array();
    foreach (array(
        'businessName', 
        'UFADGroupsDN', 
        'cn', 
        'eduperson_affiliations', 
        'eppn', 
        'givenName', 
        'displayname',
        'glid', 
        'loa', 
        'mail', 
        'middleName',
        'postalAddress',
        'sn',
        'ufid',
        'uf_affiliations',
        'primary-affiliation') as $key) 
    {
        if (isset($_SERVER[$key])) {
            $source[$_SERVER['glid']][$key] = $_SERVER[$key];
        }
    }
    return new \Shibalike\Attr\Store\ArrayStore($source);
}

function getConfig() {
    $config = new \Shibalike\Config();
    $config->idpUrl = './idp/';
    return $config;
}