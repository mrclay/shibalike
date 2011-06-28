<?php

namespace Shibalike;

use Shibalike\Event;

class AuthResult extends Event {
    
    /**
     * @param string $username
     * @param array $attrs 
     */
    public function __construct($username, array $attrs)
    {
        if (!is_string($username) || $username === '') {
            throw new \Exception("username must be a string.");
        }
        if (empty($attrs)) {
            throw new \Exception("attrs must contain at least one attribute");
        }
        $this->_username = $username;
        $this->_attrs = $attrs;
        parent::__construct();
    }
    
    /**
     * @param int $ttl
     * @return bool 
     */
    public function isFresh($ttl)
    {
        return ($this->_time + $ttl) > time();
    }
    
    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * @return array
     */
    public function getAttrs()
    {
        return $this->_attrs;
    }
    
    /**
     * @var string
     */
    protected $_username;
    
    /**
     * @var array
     */
    protected $_attrs;
}