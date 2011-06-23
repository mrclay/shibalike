<?php

namespace Shibalike;

/**
 * A value object used to store attributes & username. Created by the IdP, stored in the
 * state manager.
 */
class User {

    /**
     * @var string
     */
    protected $_username;
    
    /**
     * @var array
     */
    protected $_attrs;

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
}
