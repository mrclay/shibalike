<?php

namespace Shibalike;

/**
 * Component for populating $_SERVER vars from a state manager
 *
 * <code>
 * $sp = new Shibalike\SP(...);
 * $_SERVER = $sp->merge($_SERVER);
 *
 * // if lazy session, leave this out:
 * if (! $sp->userIsAuthenticated()) {
 *     $sp->redirect();
 * }
 *
 * // the application's shibboleth auth code runs here
 * </code>
 */
class SP {

    /**
     * @var IStateManager
     */
    protected $_stateMgr;

    /**
     * @var UrlConfig
     */
    protected $_urls;

    public function __construct(IStateManager $stateMgr, UrlConfig $urls) {
        $this->_stateMgr = $stateMgr;
        $this->_urls = $urls;
    }

    /**
     * Get the User object from the state manager
     *
     * @return User|null
     */
    public function getUser() {
        return $this->_stateMgr->getUser();
    }

    /**
     * @return bool
     */
    public function userIsAuthenticated() {
        return (bool) $this->_stateMgr->getUser();
    }

    /**
     * Close an open state manager/session and redirect the user to your shibalike login script
     *
     * @param bool $exitAfter exit after redirecting?
     */
    public function redirect($exitAfter = true) {
        $this->_stateMgr->writeClose();
        if (session_id()) {
            session_write_close();
        }
        header('Location: ' . $this->_urls->idpUrl);
        if ($exitAfter) {
            exit();
        }
    }

    /**
     * @param string $url
     */
    public function setReturnUrl($url) {
        $this->_stateMgr->setReturnUrl($url);
    }

    /**
     * Get $_SERVER merged with user attributes from the state manager
     *
     * <code>
     * $_SERVER = $sp->mergeAttrs($_SERVER);
     * </code>
     *
     * @param array $server
     * @return array
     */
    public function mergeAttrs($server) {
        $user = $this->getUser();
        if ($user) {
            $server = array_merge($server, $user->getAttrs());
            $server['REMOTE_USER'] = $user->getUsername();
        }
        return $server;
    }
}