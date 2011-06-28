<?php

namespace Shibalike;

use Shibalike\Attr\IStore;

/**
 * Component for marking user as authenticated. For use in a "login" script.
 *
 * Usage:
 * <code>
 * $idp = new Shibalike\IdP(...);
 * if (isset($_GET['logout'])) {
 *     $idp->logout();
 * }
 *
 * // try authentication somehow (e.g. using Zend_Auth)
 * if ($authenticatedSuccessfully) {
 *     $userAttrs = $idp->fetchAttrs();
 *     if ($userAttrs) {
 *         $idp->markAsAuthenticated($username);
 *         $idp->redirect();
 *     } else {
 *         // user is not in attr store!
 *     }
 * } else {
 *     // user failed authenticate!
 * }
 * </code>
 */
class IdP {

    /**
     * @param \Shibalike\IStateManager $stateMgr
     * @param \Shibalike\IStore $store
     * @param \Shibalike\Config $config
     */
    public function __construct(IStateManager $stateMgr, IStore $store, Config $config)
    {
        $this->_stateMgr = $stateMgr;
        $this->_store = $store;
        $this->_config = $config;
    }

    /**
     * Get the User object from the state manager (e.g. check if already logged in)
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
     * Fetch user attributes from the attribute store
     *
     * @param string $username
     * @return array|null
     */
    public function fetchAttrs($username)
    {
        return $this->_store->fetchAttrs($username);
    }

    /**
     * Mark the user as authenticated and store her in the state manager
     * 
     * @param string $username
     * @param array $attrs if not provided, fetchAttrs will be called
     * @return bool was the user state set successfully?
     */
    public function markAsAuthenticated($username, array $attrs = null)
    {
        if (!$attrs) {
            $attrs = $this->fetchAttrs($username);
        }
        $user = new User($username, $attrs);
        return ($this->_stateMgr->setUser($user)
                && $this->_stateMgr->setMetadata('authTime', time()));
    }

    /**
     * Get the best known URL of the shibboleth auth script
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        $url = $this->_stateMgr->getMetadata('returnUrl');
        $this->_stateMgr->setMetadata('returnUrl');
        if (empty($url)) {
            $url = $this->_config->spUrl;
        }
        return $url;
    }

    /**
     * Clear the user's state and redirect them to the logout URL
     * 
     * @param bool $redirect
     * @param bool $exitAfterRedirect
     */
    public function logout($redirect = true, $exitAfterRedirect = true)
    {
        $this->_stateMgr->forget();
        if ($redirect) {
            $this->redirect($this->_config->postLogoutUrl, $exitAfterRedirect);
        }
    }

    /**
     * Redirect the user to your shibboleth auth script
     *
     * @param bool $exitAfter exit after redirecting?
     */
    public function redirect($url = null, $exitAfter = true)
    {
        if (empty($url)) {
            $url = $this->getRedirectUrl();
        }
        if (session_id()) {
            session_write_close();
        }
        header("Location: $url");
        if ($exitAfter) {
            exit();
        }
    }
    
    /**
     * @var \Shibalike\Attr\IStore
     */
    protected $_store;
    
    /**
     * @var \Shibalike\IStateManager
     */
    protected $_stateMgr;
    
    /**
     * @var \Shibalike\Config
     */
    protected $_config;
}
