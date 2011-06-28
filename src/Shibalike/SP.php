<?php

namespace Shibalike;

use Shibalike\Junction;

/**
 * Component for populating $_SERVER vars from a state manager
 *
 * <code>
 * $sp = new Shibalike\SP(...);
 * 
 * $sp->requireValidUser(); // or $sp->initLazySession();
 * 
 * // the application's shibboleth auth code runs here
 * </code>
 */
class SP extends Junction {

    /**
     * Redirect to the IdP unless a valid user's attributes were merged in $_SERVER
     */
    public function requireValidUser()
    {
        $_SERVER = $this->mergeAttrs($_SERVER);
        if (! $this->userIsAuthenticated()) {
            $this->redirect();
        }
    }
    
    /**
     * Merge user attributes (if available) into $_SERVER
     */
    public function initLazySession()
    {
        $_SERVER = $this->mergeAttrs($_SERVER);
    }
    
    /**
     * Get $_SERVER merged with user attributes (if available)
     *
     * <code>
     * $_SERVER = $sp->mergeAttrs($_SERVER);
     * </code>
     *
     * @param array $server
     * @return array
     */
    public function mergeAttrs($server)
    {
        $user = $this->getUser();
        if ($user) {
            $server = array_merge($server, $user->getAttrs());
            $server['REMOTE_USER'] = $user->getUsername();
        }
        return $server;
    }
    
    /**
     * Get the default URL to redirect to
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_config->idpUrl;
    }
    
    /**
     * @param string $url if empty, the current URL will be used
     */
    public function setReturnUrl($url = null)
    {
        if (! $url) {
            $url = $this->getCurrentUrl();
        }
        $this->_stateMgr->setMetadata('returnUrl', $url);
    }
}
