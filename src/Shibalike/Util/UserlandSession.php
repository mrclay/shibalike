<?php

namespace Shibalike;

class Util_UserlandSession {
    
    protected $_id = '';
    
    protected $_idFromUser = '';
    
    protected $_name = 'ULSESSID';
    
    public $cookie_lifetime = 0;
    
    public $cookie_path = '/';
    
    public $cookie_domain = '';
    
    public $cookie_secure = '';
    
    public $cookie_httponly = '';
    
    public $gc_maxlifetime = 1400;
    
    public $gc_probability = 1;
    
    public $gc_divisor = 100;
    
    /**
     * Persisted session data
     * 
     * @var type array
     */
    public $data = null;
    
    /**
     * @var Util_UserlandSession_IStorage
     */
    protected $_storage;
    
    protected $_isStarted = false;

    public function __constructor(Util_UserlandSession_IStorage $storage)
    {
        $this->_storage = $storage;
    }

    public function id($id = null)
    {
        if (! $this->_id && is_string($id) && $this->_storage->idIsValid($id)) {
            $this->_idFromUser = $id;
        }
        return $this->_id;
    }
    
    public function name($name = null)
    {
        if (is_string($name) && preg_match('/^[a-zA-Z0-9]+$/', $name)) {
            if ($this->_id) {
                // must be able to persist to change name
                if ($this->_removeCookie() && $this->_setCookie($name, $this->_id)) {
                    $this->_name = $name;
                }
            } else {
                $this->_name = $name;
            }
        }
        return $this->_name;
    }
    
    public function start()
    {
        if (headers_sent()) {
            return false;
        }
        if ($this->_idFromUser) {
            $this->_setCookie($this->_name, $this->_idFromUser);
            $this->_id = $this->_idFromUser;
            $this->_idFromUser = null;
        } else {
            $id = $_COOKIE[$this->_name];
            if ($this->_storage->idIsValid($id)) {
                $this->_id = $id;
            }
        }
        // gc
        if (mt_rand(1, $this->gc_divisor) <= $this->gc_probability) {
            $this->_storage->gc($this->gc_maxlifetime);
        }
        // try to get data
        $this->_storage->open();
        if ($this->_loadData()) {
            // session has begun!
            return true;
        } else {
            // need new session id.
        }
        
    }
    
    public function regenerateId($deleteOldSession = false)
    {
        
    }
    
    /**
     * @return bool
     */
    protected function _loadData()
    {
        $stdData = $this->_storage->read($this->_id);
        if (is_string($stdData)) {
            @ $this->data = unserialize($stdData);
            if (is_array($this->data)) {
                return true;
            }
        }
        $this->data = array();
        return false;
    }


    protected function _getCookie()
    {
        return $_COOKIE[$this->_name];
    }
    
    protected function _setCookie($name, $id)
    {
        return setcookie($name, $id, time() + $this->cookie_lifetime, $this->cookie_path, $this->cookie_domain, (bool) $this->cookie_secure, (bool) $this->cookie_httponly);
    }
    
    protected function _removeCookie()
    {
        return setcookie($this->_name, '', time() - 86400, $this->cookie_path, $this->cookie_domain, (bool) $this->cookie_secure, (bool) $this->cookie_httponly);
    }
    
    
}