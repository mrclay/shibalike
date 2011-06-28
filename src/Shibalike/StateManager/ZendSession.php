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
    public function get($key)
    {
        return $this->_session->{'shibalike_' . $key};
    }
    
    /**
     * @param string $key
     * @param string $value if null, this key will be removed
     * @return bool
     */
    public function set($key, $value = null)
    {
        return $this->_session->{'shibalike_' . $key} = $value;
    }
    
    public function likelyHasState()
    {
        return \Zend_Session::sessionExists();
    }
}
