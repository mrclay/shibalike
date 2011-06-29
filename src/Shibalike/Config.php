<?php

namespace Shibalike;

class Config {

    /**
     * URL where the IdP class is used
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
}
