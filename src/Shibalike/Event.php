<?php

namespace Shibalike;

use Shibalike\Junction;

class Event {
    
    public function __construct()
    {
        $this->_url = Junction::getCurrentUrl();
        $this->_time = microtime(true);
    }
    
    public function getTime()
    {
        return $this->_time;
    }
    
    public function getUrl()
    {
        return $this->_url;
    }
    
    /**
     * @var float
     */
    protected $_time;

    /**
     * @var string
     */
    protected $_url;
}