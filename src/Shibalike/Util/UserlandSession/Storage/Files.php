<?php

namespace Shibalike;

class Util_UserlandSession_Storage_Files implements Util_UserlandSession_IStorage {
    
    const DEFAULT_PREFIX = 'SHIBALIKEID_';
    
    /**
     * @param type $path path to store session files
     * @param type $useLocking use file-locking?
     */
    public function __construct($path = null, $useLocking = true)
    {
        $this->_locking = $useLocking;
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
     * Get path for storing session files
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * @param string $_ingored
     * @param string $sessionName
     * @return bool
     */
    public function open($_ingored = '', $sessionName = '')
    {
        if ($sessionName) {
            $this->_prefix = $sessionName . '_';
        }
    }
    
    /**
     * @return bool
     */
    public function close()
    {
        $this->_prefix = self::DEFAULT_PREFIX;
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
        if (is_file($file) && ! is_writable($file)) {
            return false;
        }
        $flag = $this->_locking
            ? LOCK_EX
            : null;
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
            if (0 === strpos($entry, $this->_prefix)) {
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
        return $this->_path . DIRECTORY_SEPARATOR . $this->_prefix . $id;
    }
    
    protected $_path = null;
    
    protected $_prefix = self::DEFAULT_PREFIX;
    
    protected $_locking;
}