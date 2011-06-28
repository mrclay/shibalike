<?php

namespace Shibalike;

use Shibalike\IStateManager;
use Shibalike\Config;
use Shibalike\Attr\IStore;
use Shibalike\Junction;

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
class IdP extends Junction {

    /**
     * @param \Shibalike\IStateManager $stateMgr
     * @param \Shibalike\IStore $store
     * @param \Shibalike\Config $config
     */
    public function __construct(IStateManager $stateMgr, IStore $store, Config $config)
    {
        $this->_store = $store;
        parent::__construct($stateMgr, $config);
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
     * Get the default URL to redirect to
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_config->spUrl;
    }
    
    /**
     * Close an open state manager/session and redirect the user
     *
     * @param string $url
     * @param bool $exitAfter exit after redirecting?
     */
    public function redirect($url = null, $exitAfter = true)
    {
        if (empty($url)) {
            $url = $this->_stateMgr->getMetadata('returnUrl');
            if (! empty($url)) {
                $this->_stateMgr->setMetadata('returnUrl');
            }
        }
        parent::redirect($url, $exitAfter);
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
     * @var \Shibalike\Attr\IStore
     */
    protected $_store;
}
