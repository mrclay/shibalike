<?php

namespace Shibalike\StateManager;

use Shibalike\IStateManager;
use Shibalike\User;
use Shibalike\Util\UserlandSession as Sess;

class UserlandSession implements IStateManager {

    /**
     * @var \Shibalike\Util\UserlandSession
     */
    protected $_session;

    /**
     * @param \Shibalike\Util\UserlandSession $session 
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
        $this->_session->write_close();
    }
    
    /**
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        if (! $this->_session->id()) {
            if ($this->_session->session_likely_exists()) {
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
        return $this->_session->session_likely_exists();
    }
}
