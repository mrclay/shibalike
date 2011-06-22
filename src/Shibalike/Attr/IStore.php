<?php

namespace Shibalike\Attr;

interface IStore {

    /**
     * Return "shibboleth" attributes for a user
     * 
     * @return array|null associative array with strings keys
     */
    public function fetchAttrs($username);
}
