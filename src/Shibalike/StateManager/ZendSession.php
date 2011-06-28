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

    public function forget()
    {
        $this->_session->unsetAll();
    }

    public function writeClose()
    {
        \Zend_Session::writeClose();
    }
    
    /**
     * @param string $key
     * @return string|null
     */
    public function getMetadata($key)
    {
        return $this->_session->{'shibalikeMeta_' . $key};
    }
    
    /**
     * @param string $key
     * @param string $value if null, this key will be removed
     * @return bool
     */
    public function setMetadata($key, $value = null)
    {
        return $this->_session->{'shibalikeMeta_' . $key} = $value;
    }
}
