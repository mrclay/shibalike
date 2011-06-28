<?php

namespace Shibalike;

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
class SP {

    /**
     * @param \Shibalike\IStateManager $stateMgr
     * @param \Shibalike\Config $config 
     */
    public function __construct(IStateManager $stateMgr, Config $config)
    {
        $this->_stateMgr = $stateMgr;
        $this->_config = $config;
    }
    
    /**
     * Redirect to the IdP unless a valid user's attributes were merged in $_SERVER
     */
    public function requireValidUser()
    {
        $_SERVER = $this->mergeAttrs($_SERVER);
        if (! $this->userIsAuthenticated()) {
            $this->setReturnUrl();
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
     * @return bool
     */
    public function userIsAuthenticated()
    {
        return (bool) $this->getUser();
    }

    /**
     * Get the User object from the state manager
     *
     * @return \Shibalike\User|null
     */
    public function getUser()
    {
        $authTime = $this->_stateMgr->getMetadata('authTime');
        if (($authTime + $this->_config->timeout) < time()) {
            return $this->_stateMgr->getUser();
        }
    }

    /**
     * Close an open state manager/session and redirect the user to your shibalike login script
     *
     * @param bool $exitAfter exit after redirecting?
     */
    public function redirect($exitAfter = true)
    {
        $this->_stateMgr->writeClose();
        if (session_id()) {
            session_write_close();
        }
        header('Location: ' . $this->_config->idpUrl);
        if ($exitAfter) {
            exit();
        }
    }

    /**
     * @param string $url if empty, the current URL will be used
     */
    public function setReturnUrl($url = null)
    {
        if (! $url) {
            $url = $this->_getCurrentUrl();
        }
        $this->_stateMgr->setMetadata('returnUrl', $url);
    }
    
    /**
     * @return string
     */
    protected function _getCurrentUrl()
    {
        $host  = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=="off") ? 'https' : 'http';
        $port  = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
        $uri   = $proto . '://' . $host;
        if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
            $uri .= ':' . $port;
        }
        return $uri . $_SERVER['REQUEST_URI'];
    }

    /**
     * @var IStateManager
     */
    protected $_stateMgr;
    
    /**
     * @var Config
     */
    protected $_config;
}
