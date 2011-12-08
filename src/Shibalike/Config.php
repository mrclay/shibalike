<?php

namespace Shibalike;

class Config {

    /**
     * URL where the IdP class is used to handle SP auth requests
     * @var string
     */
    public $idpUrl = "";
    
    /**
     * Seconds after authTime when the User will no longer be considered authenticated.
     * @var int
     */
    public $timeout = 28800;
    
    /**
     * @var string no logging if empty
     */
    public $logFile = "";

    /**
     * Value merged as $_SERVER['Shib-Identity-Provider'] when attributes are merged.
     * Some Shibboleth adapters expect something to be at that key.
     * @var string
     */
    public $shibIdentityProvider = "/idp/shibalike";
}
