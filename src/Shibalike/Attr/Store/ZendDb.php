<?php

namespace Shibalike\Attr\Store;

use Shibalike\Attr\IStore;

class ZendDb implements IStore {

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $_db;
    
    /**
     * @var string
     */
    protected $_prefix;

    public function __construct(\Zend_Db_Adapter_Abstract $db, $prefix = 'shibalike_')
    {
        $this->_db = $db;
        $this->_prefix = $prefix;
    }

    /**
     * @param type $username
     * @return type 
     */
    public function fetchAttrs($username)
    {
        $sql = "SELECT `key`, `value` FROM {$this->_prefix}attributes WHERE username = ?";
        $attrs = $this->_db->fetchPairs($sql, $username);
        if (empty($attrs)) {
            return null;
        }
        return $attrs;
    }
    
    /**
     * Specify character encoding in queries/responses. You may want to call this on your
     * Zend adapter before injecting it into Shibalike\Attr\Store\ZendDb.
     * 
     * @param \Zend_Db_Adapter_Abstract $db
     * 
     * @param string $encoding 
     */
    public static function setEncodingMysql(\Zend_Db_Adapter_Abstract $db, $encoding = 'utf8')
    {
        $db->query("SET NAMES '$encoding'");
        $db->query("SET CHARACTER SET '$encoding'");    
    }
}