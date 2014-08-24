<?php

namespace Shibalike\StateManager;

use Shibalike\IStateManager;
use UserlandSession\Session as Sess;

class UserlandSession implements IStateManager {

    /**
     * @var Sess
     */
    protected $_session;

    /**
     * @param Sess $session
     */
    public function __construct(Sess $session)
    {
        $this->_session = $session;
    }

    public function forget()
    {
        if (! $this->_session->id()) {
            $this->_session->start();
        }
        $this->_session->destroy(true);
    }

    public function writeClose()
    {
        $this->_session->writeClose();
    }
    
    /**
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        if (! $this->_session->id()) {
            if ($this->_session->sessionLikelyExists()) {
                $this->_session->start();
            } else {
                return null;
            }
        }
        $key = 'shibalike_' . $key;
        return isset($this->_session->data[$key])
            ? $this->_session->data[$key]
            : null;
    }
    
    /**
     * @param string $key
     * @param string $value if null, this key will be removed
     * @return bool
     */
    public function set($key, $value = null)
    {
        if (! $this->_session->id()) {
            $this->_session->start();
        }
        $key = 'shibalike_' . $key;
        if ($value === null) {
            unset($this->_session->data[$key]);
        } else {
            $this->_session->data[$key] = $value;
        }
        return true;
    }
    
    public function likelyHasState()
    {
        return $this->_session->sessionLikelyExists();
    }

    public function getSessionId()
    {
        return $this->_session->id();
    }
}
