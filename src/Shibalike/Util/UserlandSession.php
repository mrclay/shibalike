<?php

namespace Shibalike;

/**
 * A PHP emulation of native session behavior. Other than HTTP IO (header() and 
 * setcookie(), there's no global state in this implementation; you can have an active
 * session beside another instance or beside the native session.
 * 
 * Only name and id have get/setter functions. The other options are public properties.
 * 
 * There's no handler/module because one has to inject a storage handler into the
 * constructor. This is why save_path was moved to the Files handler. 
 * 
 * The biggest difference is that you can set cache_limiter = '', meaning no headers 
 * (other than Set-Cookie) will be sent at start(). This may be useful if you need to use 
 * this class in tandem with native sessions.
 * 
 * Also a tiny session fixation vulnerability has been prevented in start().
 */
class Util_UserlandSession {
    
    const CACHE_LIMITER_NONE = '';
    const CACHE_LIMITER_PUBLIC = 'public';
    const CACHE_LIMITER_PRIVATE_NO_EXPIRE = 'private_no_expire';
    const CACHE_LIMITER_PRIVATE = 'private';
    const CACHE_LIMITER_NOCACHE = 'nocache';

    public $cookie_lifetime = 0;
    public $cookie_path = '/';
    public $cookie_domain = '';
    public $cookie_secure = '';
    public $cookie_httponly = '';
    public $gc_maxlifetime = 1400;
    public $gc_probability = 1;
    public $gc_divisor = 100;
    public $cache_limiter = self::CACHE_LIMITER_NOCACHE;
    public $cache_expire = 180;
    
    /**
     * Persisted session data
     * 
     * @var type array
     */
    public $data = null;
    
    /**
     * @var Util_UserlandSession_IStorage
     */
    protected $_storage;

    /**
     * @param Util_UserlandSession_IStorage $storage When using the Files handler, make
     * sure to use a separate instance for different session names. If you re-use the
     * instance, you could end up accessing files under the wrong prefix. 
     */
    public function __construct(Util_UserlandSession_IStorage $storage)
    {
        $this->_storage = $storage;
    }

    /**
     * Get the session ID, or set an ID to be used when the session
     * begins. When setting, the format is validated by the storage handler.
     * 
     * @param string $id
     * @return string ('' means there is no active session)
     */
    public function id($id = null)
    {
        if (! $this->_id && is_string($id) && $this->_storage->idIsValid($id)) {
            $this->_idFromUser = $id;
        }
        return $this->_id;
    }
    
    /**
     * Get the session name (name used in the cookie), or set it. If the session is active,
     * this must be called before any output is sent.
     * 
     * @param string $name
     * @return type 
     */
    public function name($name = null)
    {
        if (is_string($name) && preg_match('/^[a-zA-Z0-9]+$/', $name)) {
            if ($this->_id) {
                // must be able to persist to change name
                if ($this->remove_cookie() && $this->_set_cookie($name, $this->_id)) {
                    $this->_name = $name;
                }
            } else {
                $this->_name = $name;
            }
        }
        return $this->_name;
    }
    
    /**
     * Get a session ID from the client that's been validated by the storage handler.
     * 
     * @return string
     */
    public function get_id_from_cookie()
    {
        if (! empty($_COOKIE[$this->_name])) {
            $id = $_COOKIE[$this->_name];
            if (is_string($id) && $this->_storage->idIsValid($id)) {
                return $id;
            }
        }
        return false;
    }
    
    /**
     * Does the storage handler have data under this ID?
     * 
     * @param string $id
     * @return bool
     */
    public function persisted_data_exists($id)
    {
        if (! $this->_id) {
            $this->_storage->open('', $this->_name);
        }
        $ret = (bool) $this->_storage->read($id);
        if (! $this->_id) {
            $this->_storage->close();
        }
        return $ret;
    }
    
    /**
     * Is the client's ID valid and pointing to existing session data? You might want to
     * call this if you don't want to start sessions for every visitor.
     * 
     * @return bool
     */
    public function session_likely_exists()
    {
        $id = $this->get_id_from_cookie();
        return $id && $this->persisted_data_exists($id);
    }
    
    /**
     * Start the session.
     * 
     * @return bool success
     */
    public function start()
    {
        if (headers_sent()) {
            return false;
        }
        $this->data = array();
        if ($this->_idFromUser) {
            $this->_set_cookie($this->_name, $this->_idFromUser);
            $this->_id = $this->_idFromUser;
            $this->_idFromUser = null;
        } else {
            $id = $this->get_id_from_cookie();
            if ($id) {
                $this->_id = $id;
            }
        }
        // should we call GC?
        $rand = mt_rand(1, $this->gc_divisor);
        if ($rand <= $this->gc_probability) {
            $this->_storage->gc($this->gc_maxlifetime);
        }
        // open storage
        $this->_storage->open('', $this->_name);
        
        // try data fetch
        if (! $this->_load_data()) {   
            // unlike the native PHP session, we don't let users choose their own
            // session IDs if there's no data. This prevents session fixation through 
            // cookies (very hard for an attacker, but why leave this door open?).
            $this->_id = self::generate_new_id();
            $this->_set_cookie($this->_name, $this->_id);
        }
        // send optional cache limiter
        
        // this is actual session behavior rather than what's documented.
        $lastModified = self::gmt_format(filemtime($_SERVER['SCRIPT_FILENAME']));
        
        $ce = $this->cache_expire;
        switch ($this->cache_limiter) {
            case self::CACHE_LIMITER_PUBLIC:
                header('Expires: ' . self::gmt_format(time() + $ce));
                header("Cache-Control: public, max-age=$ce");
                header('Last-Modified: ' . $lastModified);
                break;
            case self::CACHE_LIMITER_PRIVATE_NO_EXPIRE:
                header("Cache-Control: private, max-age=$ce, pre-check=$ce");
                header('Last-Modified: ' . $lastModified);
                break;
            case self::CACHE_LIMITER_PRIVATE:
                header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
                header("Cache-Control: private, max-age=$ce, pre-check=$ce");
                header('Last-Modified: ' . $lastModified);
                break;
            case self::CACHE_LIMITER_NOCACHE:
                header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
                header('Pragma: no-cache');
                break;
            case self::CACHE_LIMITER_NONE:
                // send no cache headers, please
                break;
        }
        return true;
    }
    
    /**
     * Write data and close the session. (This is called automatically by the destructor,
     * but for the sake of proper serialization, you should call it explicitly)
     * 
     * @return bool success
     */
    public function write_close()
    {
        if (! $this->_id || ! $this->_save_data()) {
            return false;
        }
        $this->_storage->close();
        $this->_id = '';
        return true;
    }
    
    public function __destruct() {
        if ($this->_id) {
            $this->write_close();
        }
    }

    /**
     * Stop the session and destroy its persisted data.
     * 
     * @param bool $removeCookie Remove the session cookie, too?
     * @return bool success
     */
    public function destroy($removeCookie = false)
    {
        if ($this->_id) {
            if ($removeCookie) {
                $this->remove_cookie();
            }
            $this->_storage->destroy($id);
            $this->_storage->close();
            $this->_id = '';
            return true;
        }
        return false;
    }
    
    /**
     * Regenerate the session ID, update the browser's cookie, and optionally remove the
     * previous ID's session storage.
     * 
     * @param bool $delete_old_session
     * @return bool success
     */
    public function regenerate_id($delete_old_session = false)
    {
        if (headers_sent() || ! $this->_id) {
            return false;
        }
        $this->remove_cookie();
        $oldId = $this->_id;
        $this->_id = self::generate_new_id();
        $this->_set_cookie($this->_name, $this->_id);
        if ($oldId && $delete_old_session) {
            $this->_storage->destroy($oldId);
        }
        return true;
    }
    
    /**
     * Remove the session cookie
     * 
     * @return bool success
     */
    public function remove_cookie()
    {
        return setcookie($this->_name, '', time() - 86400, $this->cookie_path, $this->cookie_domain, (bool) $this->cookie_secure, (bool) $this->cookie_httponly);
    }
    
    /**
     * Create a random alphanumeric string
     * 
     * @param int $length
     * @return string 
     */
    public static function generate_new_id($length = 40)
	{
        // generate 256 random bytes (adapted from phpass)
        $numBytes = 256;
        $randomState = microtime();
        if (function_exists('getmypid')) {
			$randomState .= getmypid();
        }        
        $bytes = '';
        if (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
            $bytes = fread($fh, $numBytes);
            fclose($fh);
        }
        if (strlen($bytes) < $numBytes) {
            $bytes = '';
            for ($i = 0; $i < $numBytes; $i += 16) {
                $randomState = md5(microtime() . $randomState);
                $bytes .= pack('H*', md5($randomState));
            }
            $bytes = substr($bytes, 0, $numBytes);
        }
        // convert bytes to base64, strip non-alphanumerics), return a random chunk
        $base64 = str_replace(array('+', '/', '='), '', base64_encode($bytes));
        $start = mt_rand(0, strlen($base64) - $length);
        return substr($base64, $start, $length);
	}
    
    /**
     * Get a GMT formatted date for use in HTTP headers
     * 
     * @param int $time unix timestamp
     * @return string
     */
    public static function gmt_format($time)
    {
        return gmdate('D, d M Y H:i:s \G\M\T', $time);
    }
    
    /**
     * @return bool
     */
    protected function _load_data()
    {
        $serialization = $this->_storage->read($this->_id);
        if (is_string($serialization)) {
            $this->data = unserialize($serialization);
            if (is_array($this->data)) {
                return true;
            }
        }
        $this->data = array();
        return false;
    }
    
    /**
     * @return bool
     */
    protected function _save_data()
    {
        $strData = serialize($this->data);
        return $this->_storage->write($this->_id, $strData);
    }

    /**
     * @return bool
     */
    protected function _set_cookie($name, $id)
    {
        $expire = $this->cookie_lifetime
            ? time() + (int) $this->cookie_lifetime
            : 0;
        return setcookie($name, $id, $expire, $this->cookie_path, $this->cookie_domain, (bool) $this->cookie_secure, (bool) $this->cookie_httponly);
    }
    
    /**
     * Active session ID, or empty if inactive
     * 
     * @var string
     */
    protected $_id = '';
    
    /**
     * User has requested this be used as ID for the next session
     * 
     * @var string 
     */
    protected $_idFromUser = '';
    
    protected $_name = 'SHIBALIKEID';
}