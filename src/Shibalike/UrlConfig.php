<?php

namespace Shibalike;

class UrlConfig {

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
}
