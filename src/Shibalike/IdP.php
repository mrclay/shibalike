<?php

namespace Shibalike;

use Shibalike\Attr\IStore;

/**
 * Component for marking user as authenticated in the state manager with attributes pulled
 * from a store. For use in a "login" script.
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
     * @var \Shibalike\Attr\IStore
     */
    protected $_store;

    /**
     * @var \Shibalike\IStateManager
     */
    protected $_stateMgr;

    /**
     * @var UrlConfig
     */
    protected $_urls;

    public function __construct(IStateManager $stateMgr, IStore $store, UrlConfig $urls) {
        $this->_stateMgr = $stateMgr;
        $this->_store = $store;
        $this->_urls = $urls;
    }

    /**
     * Get the User object from the state manager (e.g. check if already logged in)
     *
     * @return User|null
     */
    public function getUser() {
        return $this->_stateMgr->getUser();
    }
    
    /**
     * Fetch user attributes from the attribute store
     *
     * @param string $username
     * @return array|null
     */
    public function fetchAttrs($username) {
        return $this->_store->fetchAttrs($username);
    }
    
    /**
     * Mark the user as authenticated and store her in the state manager
     * 
     * @param string $username
     * @param array $attrs 
     * @return bool was the user state set successfully?
     */
    public function markAsAuthenticated($username, array $attrs = null) {
        if (! $attrs) {
            $attrs = $this->fetchAttrs($username);
        }
        $user = new User($username, $attrs);
        return $this->_stateMgr->setUser($user);
    }

    /**
     * Get the best known URL of the shibboleth auth script
     *
     * @return string
     */
    public function getRedirectUrl() {
        $url = $this->_stateMgr->getReturnUrl();
        $this->_stateMgr->setReturnUrl("");
        if (empty($url)) {
            $url = $this->_urls->spUrl;
        }
        return $url;
    }

    /**
     * @param bool $redirect
     * @param bool $exitAfterRedirect
     */
    public function logout($redirect = true, $exitAfterRedirect = true) {
        $this->_stateMgr->forget();
        if ($redirect) {
            $this->redirect($this->_urls->postLogoutUrl, $exitAfterRedirect);
        }
    }

    /**
     * Redirect the user to your shibboleth auth script
     *
     * @param bool $exitAfter exit after redirecting?
     */
    public function redirect($url = null, $exitAfter = true) {
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
}

