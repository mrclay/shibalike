<?php

namespace Shibalike;

class StateManager_ZendSession implements IStateManager {

    /**
     * @var \Zend_Session_Namespace
     */
    protected $_session;

    public function __construct(\Zend_Session_Namespace $session)
    {
        $this->_session = $session;
    }

    public function setUser(User $user)
    {
        $this->_session->shibalikeUser = $user;
    }

    public function getUser()
    {
        return $this->_session->shibalikeUser;
    }

    public function unsetUser()
    {
        $this->_session->shibalikeUser = null;
        return true;
    }

    public function setReturnUrl($url)
    {
        $this->_session->returnUrl = $url;
    }

    public function getReturnUrl()
    {
        return $this->_session->returnUrl;
    }

    public function forget()
    {
        $this->_session->unsetAll();
    }

    public function writeClose()
    {
        \Zend_Session::writeClose();
    }
}
