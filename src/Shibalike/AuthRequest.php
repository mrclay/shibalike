<?php

namespace Shibalike;

use Shibalike\Event;

class AuthRequest extends Event {
    
    public function __construct($returnUrl = null)
    {
        $this->_returnUrl = $returnUrl;
        parent::__construct();
    }
    
    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_returnUrl;
    }
    
    /**
     * @var string
     */
    protected $_returnUrl;
}