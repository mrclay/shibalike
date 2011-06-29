<?php

namespace Shibalike;

use Shibalike\IStateManager;
use Shibalike\Config;

/**
 * 
 */
class Junction {

    /**
     * @param \Shibalike\IStateManager $stateMgr
     * @param \Shibalike\Config $config 
     */
    public function __construct(IStateManager $stateMgr, Config $config)
    {
        $this->_stateMgr = $stateMgr;
        $this->_config = $config;
        if (! empty($config->logFile)) {
            $log = new \Zend_Log(new \Zend_Log_Writer_Stream($config->logFile));
            $this->setLog($log);
        }
    }
    
    /**
     * @return bool
     */
    public function userIsAuthenticated()
    {
        return (bool) $this->getValidAuthResult();
    }

    /**
     * Get the User object from the state manager
     *
     * @return \Shibalike\AuthResult|null
     */
    public function getValidAuthResult()
    {
        $authResult = $this->_stateMgr->get('authResult');
        if ($authResult && $authResult->isFresh($this->_config->timeout)) {
            return $authResult;
        }
        return null;
    }

    /**
     * Close an open state manager/session and redirect the user
     *
     * @param string $url
     * @param bool $exitAfter exit after redirecting?
     */
    public function redirect($url = null, $exitAfter = true)
    {
        if (empty($url)) {
            $url = $this->getRedirectUrl();
        }
        $this->_stateMgr->writeClose();
        if (session_id()) {
            session_write_close();
        }
        header("Location: $url");
        if ($exitAfter) {
            exit();
        }
    }
    
    /**
     * Clear the user's state
     */
    public function logout()
    {
        $this->_stateMgr->forget();
    }
    
    /**
     * Get the default URL to redirect to
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return null;
    }
    
    /**
     * @return string
     */
    public static function getCurrentUrl()
    {
        $host  = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=="off") ? 'https' : 'http';
        $port  = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
        $uri   = $proto . '://' . $host;
        if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
            $uri .= ':' . $port;
        }
        return $uri . $_SERVER['REQUEST_URI'];
    }
    
    public function setLog(\Zend_Log $log) {
        $this->_log = $log;
    }

    /**
     * @var \Shibalike\IStateManager
     */
    protected $_stateMgr;
    
    /**
     * @var \Shibalike\Config
     */
    protected $_config;
    
    /**
     * @var \Zend_Log
     */
    protected $_log;
}
