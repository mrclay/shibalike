<?php

namespace Shibalike\Util\UserlandSession\Storage;

use Shibalike\Util\UserlandSession\IStorage;

/**
 * File session storage.
 */
class Files implements IStorage {

    /**
     * @param string $name session name (to be used in cookie)
     * @param array $options for the storage container
     *    'flock' : lock files for read/write (true by default)
     *    'path' : save path for files
     */
    public function __construct($name = 'SHIBALIKEID', array $options = array())
    {
        $this->_name = $name;
        $this->_locking = isset($options['flock']) ? (bool) $options['flock'] : true;
        $path = empty($options['path']) ? null : $options['path'];
        if (is_string($path)) {
            $path = rtrim(preg_replace('/^\\d+;/', '', $path), '/\\');
            if ($this->_isValidPath($path)) {
                $this->_path = $path;
            } else {
                throw new \Exception('Path is not valid or is not writable: ' . $path);
            }
        }
        if (null === $this->_path) {
            // hashed directory tree not supported
            $path = rtrim(preg_replace('/^\\d+;/', '', ini_get('session.save_path')), '/\\');
            if ($this->_isValidPath($path)) {
                $this->_path = $path;
            } else {
                $this->_path = rtrim(sys_get_temp_dir(), '/\\');
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get path for storing session files
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @return bool
     */
    public function open()
    {
        
    }

    /**
     * @return bool
     */
    public function close()
    {
        
    }

    /**
     * @param string $id
     * @return string|false
     */
    public function read($id)
    {
        $file = $this->_getFilePath($id);
        if (is_file($file) && is_readable($file)) {
            if ($this->_locking) {
                $fp = fopen($file, 'rb');
                flock($fp, LOCK_SH);
                $ret = stream_get_contents($fp);
                flock($fp, LOCK_UN);
                fclose($fp);
                return $ret;
            } else {
                return file_get_contents($file);
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
        $file = $this->_getFilePath($id);
        if (is_file($file) && !is_writable($file)) {
            return false;
        }
        $flag = $this->_locking ? LOCK_EX : null;
        return (bool) file_put_contents($file, $data, $flag);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $file = $this->_getFilePath($id);
        if (is_file($file) && is_writable($file)) {
            return unlink($file);
        }
        return false;
    }

    /**
     * @param int $maxLifetime
     * @return bool
     */
    public function gc($maxLifetime)
    {
        $d = dir($this->_path);
        //echo "Path: " . $d->path . "\n";
        $t = time();
        while (false !== ($entry = $d->read())) {
            if (0 === strpos($entry, $this->_name . '_')) {
                $file = $this->_path . DIRECTORY_SEPARATOR . $entry;
                $mtime = filemtime($file);
                if (false !== $mtime) {
                    $lifetime = $t - $mtime;
                    if ($lifetime > $maxLifetime && is_writable($file)) {
                        unlink($file);
                    }
                }
            }
        }
        $d->close();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function idIsValid($id)
    {
        return preg_match('/^[a-zA-Z0-9\\-\\_]+$/', $id);
    }

    protected function _isValidPath($path)
    {
        return $path && is_dir($path) && is_writable($path);
    }

    protected function _getFilePath($id)
    {
        return $this->_path . DIRECTORY_SEPARATOR . $this->_name . '_' . $id;
    }

    /**
     * @var string
     */
    protected $_path = null;
    
    /**
     * @var bool
     */
    protected $_locking;
    
    /**
     * @var string
     */
    protected $_name;
}