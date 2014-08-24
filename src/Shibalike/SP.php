<?php

namespace Shibalike;

use Shibalike\StateManager\UserlandSession as UserlandSessionStateMgr;
use UserlandSession\SessionBuilder;

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

    public $username = null;
    
    public $userAttrs = array();
    
    /**
     * Redirect to the IdP unless a valid user's attributes were merged in $_SERVER
     */
    public function requireValidUser()
    {
        $_SERVER = $this->mergeAttrs($_SERVER);
        if (! $this->userIsAuthenticated()) {
            $this->makeAuthRequest();
            $this->redirect();
        }
    }
    
    /**
     * Merge user attributes (if available) into $_SERVER
     */
    public function initLazySession()
    {
        if ($this->_stateMgr->likelyHasState()) {
            $_SERVER = $this->mergeAttrs($_SERVER);
        }
    }
    
    /**
     * Get $_SERVER merged with user attributes (if available), and set
     * username/userAttrs properties.
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
        $authResult = $this->getValidAuthResult();
        if ($authResult) {
            $this->userAttrs = $authResult->getAttrs();
            $server = array_merge($server, $this->userAttrs);
            if (! empty($this->_config->shibIdentityProvider)) {
                $server['Shib-Identity-Provider'] = $this->_config->shibIdentityProvider;
            }
            $server['REMOTE_USER'] = $this->username = $authResult->getUsername();
            $server['Shib-Session-ID'] = $this->_stateMgr->getSessionId();
        }
        return $server;
    }
    
    /**
     * Instruct IdP that this user wishes to be authenticated
     * 
     * @param string $returnUrl if null, getReturnUrl() is used
     */
    public function makeAuthRequest($returnUrl = null)
    {
        if (empty($returnUrl)) {
            $returnUrl = $this->getReturnUrl();
        }
        $this->_stateMgr->set('authRequest', new AuthRequest($returnUrl));
        $this->_stateMgr->set('authResult');
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
     * @param string $url
     */
    public function setReturnUrl($url)
    {
        $this->_returnUrl = $url;
    }
    
    public function getReturnUrl()
    {
        return empty($this->_returnUrl)
            ? Junction::getCurrentUrl()
            : $this->_returnUrl;
    }

    /**
     * Creates an SP that stores session data in files
     * @param string $idpUrl URL where the IdP class is used to handle SP auth requests
     * @param string $cookieName
     * @param string $sessionPath path where session files are stored
     * @return SP|false false if a UserlandSession already exists under this cookie name
     */
    public static function createFileBased($idpUrl, $cookieName = 'SHIBALIKE', $sessionPath = null)
    {
        if (empty($sessionPath)) {
            $sessionPath = sys_get_temp_dir();
        }
		$session = SessionBuilder::instance()
			->setName($cookieName)
			->setSavePath($sessionPath)
			->build();
		$stateMgr = new UserlandSessionStateMgr($session);
        $config = new Config();
        $config->idpUrl = $idpUrl;

        return new self($stateMgr, $config);
    }
    
    /**
     * @var string
     */
    protected $_returnUrl;
}
