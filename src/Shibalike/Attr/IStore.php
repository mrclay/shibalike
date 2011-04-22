<?php

namespace Shibalike;

interface Attr_IStore {

    /**
     * Return "shibboleth" attributes for a user
     * 
     * @return array|null associative array with strings keys
     */
    public function fetchAttrs($username);
}
