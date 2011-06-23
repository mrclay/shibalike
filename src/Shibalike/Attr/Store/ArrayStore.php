<?php

namespace Shibalike\Attr\Store;

use Shibalike\Attr\IStore;

class ArrayStore implements IStore {

    protected $_storage = array();

    public function __construct(array $source)
    {
        $this->_storage = $source;
    }

    public function fetchAttrs($username)
    {
        if (!isset($this->_storage[$username])) {
            return null;
        }
        return $this->_storage[$username];
    }

}