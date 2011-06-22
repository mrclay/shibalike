<?php

namespace Shibalike;

class User {
    protected $_username;
    protected $_attrs;

    public function __construct($username, array $attrs) {
        if (! is_string($username) || $username === '') {
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
    public function getUsername() {
        return $this->_username;
    }

    /**
     * @return array
     */
    public function getAttrs() {
        return $this->_attrs;
    }
}
