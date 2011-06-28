<?php

namespace Shibalike;

class Config {

    /**
     * URL where IdP is used
     * @var string
     */
    public $idpUrl = "";
    
    /**
     * URL of app with Shibboleth login
     * @var string
     */
    public $spUrl = "";
    
    /**
     * URL to forward to after logout
     * @var string
     */
    public $postLogoutUrl = "";
    
    /**
     * Seconds after authTime when the User will no longer be considered authenticated.
     * @var int
     */
    public $timeout = 28800;
    
    /**
     * @var string no logging if empty
     */
    public $logFile = "";
}
