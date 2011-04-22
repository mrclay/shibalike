<?php

namespace Shibalike;

class Attr_Store_Array implements \Shibalike\Attr_IStore {
    protected $_storage = array();

    public function __construct(array $source) {
        $this->_storage = $source;
    }

    public function fetchAttrs($username) {
        if (! isset($this->_storage[$username])) {
            return null;
        }
        return $this->_storage[$username];
    }
}