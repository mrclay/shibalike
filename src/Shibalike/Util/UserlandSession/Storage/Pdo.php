<?php

namespace Shibalike\Util\UserlandSession\Storage;

use Shibalike\Util\UserlandSession\IStorage;

/**
 * File session storage.
 */
class Pdo implements IStorage {

    /**
     * @param string $name session name (to be used in cookie)
     * @param array $options for the storage container
     *   'dns' : argument for PDO::__construct
     *   'username' : argument for PDO::__construct
     *   'password' : argument for PDO::__construct
     *   'driver_options' : argument for PDO::__construct
     *   'dbname'
     *   'table'
     */
    public function __construct($name = 'SHIBALIKEID', array $options = array())
    {
        $this->_name = $name;
        $this->_options = array_merge(array(
            'username' => null,
            'password' => null,
            'driver_options' => null,
            'table' => 'shibalike_sessions',
        ), $options);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return bool
     */
    public function open()
    {
        $o = $this->_options;
        try {
            $this->_pdo = new \PDO($o['dsn'], $o['username'], $o['password'], $o['driver_options']);
        } catch (\PDOException $e) {
            throw new \Exception("Could not connect to PDO: " . $e->getMessage());
        }
        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        $this->_pdo = null;
    }

    /**
     * @param string $id
     * @return string|false
     */
    public function read($id)
    {
        $sql = "
            SELECT `data` 
            FROM `{$this->_options['table']}`
            WHERE `id` = " . $this->_pdo->quote($id) . "
              AND `name` = " . $this->_pdo->quote($this->_name) . "
        ";
        $stmt = $this->_pdo->query($sql);
        if ($stmt) {
            foreach ($this->_pdo->query($sql) as $row) {
                return $row['data'];
            }    
        }
        return false;
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        $sql = "
            REPLACE INTO `{$this->_options['table']}`
            VALUES (
                " . $this->_pdo->quote($id) . ",
                " . $this->_pdo->quote($this->_name) . ",
                " . $this->_pdo->quote($data) . ",
                '" . time() . "')
        ";
        return (bool) $this->_pdo->exec($sql);
        
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $sql = "
            DELETE FROM `{$this->_options['table']}`
            WHERE `id` = " . $this->_pdo->quote($id) . "
              AND `name` = " . $this->_pdo->quote($this->_name) . "
        ";
        return (bool) $this->_pdo->exec($sql);
    }

    /**
     * @param int $maxLifetime
     * @return bool
     */
    public function gc($maxLifetime)
    {
        $sql = "
            DELETE FROM `{$this->_options['table']}`
            WHERE `name` = " . $this->_pdo->quote($this->_name) . "
              AND `time` < " . (int) (time() - $maxLifetime) . "
        ";
        return (bool) $this->_pdo->exec($sql);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function idIsValid($id)
    {
        return preg_match('/^[a-zA-Z0-9\\-\\_]+$/', $id);
    }

    /**
     * @var string
     */
    protected $_name;
    
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var \PDO
     */
    protected $_pdo;
}