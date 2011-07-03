<?php

namespace Shibalike\Util;

use Shibalike\Util\UserlandSession\IStorage;
use Shibalike\Util\IdGenerator;
use Shibalike\Util\UserlandSession\Storage\Files;

/**
 * A PHP emulation of native session behavior. Other than HTTP IO (header() and 
 * setcookie(), there's no global state in this implementation; you can have an active
 * session beside another instance or beside the native session.
 * 
 * Only id has a get/setter function. The other options are public properties.
 * 
 * There's no set_handler/module because one has to inject a storage handler into the
 * constructor. This also moved the save_path option to the Files handler.
 * 
 * Session name is set in the storage handler, which prevents the user from mistakenly
 * re-using a storage handler for multiple sessions.  
 * 
 * The biggest usage difference is that you can set cache_limiter = '', meaning no headers 
 * (other than Set-Cookie) will be sent at start(). This may be useful if you need to use 
 * this class in tandem with native sessions.
 * 
 * Also a tiny session fixation vulnerability has been prevented in start().
 *
 * @see http://svn.php.net/viewvc/php/php-src/trunk/ext/session/session.c?view=markup
 */
class UserlandSession {
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
    public $id_length = 40;
    public $cache_limiter = self::CACHE_LIMITER_NOCACHE;
    public $cache_expire = 180;
    
    /**
     * Persisted session data. Alter this file after start()-ing the session
     * 
     * @var type array
     */
    public $data = null;

    /**
     * @return Shibalike\Util\UserlandSession\IStorage
     */
    public function get_storage()
    {
        return $this->_storage;
    }

    /**
     * Users should consider using factory() to prevent cookie/storage name collisions.
     * 
     * @param Shibalike\Util\UserlandSession\IStorage $storage
     */
    public function __construct(IStorage $storage)
    {
        $this->_storage = $storage;
        $this->_name = $storage->getName();
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->_name)) {
            throw new \Exception('UserlandSession\\Storage name may contain only a-zA-Z_');
        }
    }

    /**
     * More safely create a session. This function will only let you create sessions with 
     * names that are unique (case-insentively) to avoid creating cookie/storage 
     * collisions. It also forbids using a name that matches the global setting session.name.
     * 
     * @param Shibalike\Util\UserlandSession\IStorage $storage (will use Files if not specified)
     * @return Shibalike\Util\UserlandSession
     */
    public static function factory(IStorage $storage = null)
    {
        static $activeNames = array();
        static $i = 1;

        if (null === $storage) {
            $storage = new Files("SHIBALIKE$i");
            $i++;
        }
        $activeNames[strtoupper(ini_get('session.name'))] = true;
        $name = strtoupper($storage->getName());
        if (isset($activeNames[$name])) {
            return false;
        }
        $activeNames[$name] = true;
        return new self($storage);
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
        if (!$this->_id && is_string($id) && $this->_storage->idIsValid($id)) {
            $this->_requestedId = $id;
        }
        return $this->_id;
    }

    /**
     * Get a session ID from the client that's been validated by the storage handler.
     * 
     * @return string
     */
    public function get_id_from_cookie()
    {
        if (!empty($_COOKIE[$this->_name])) {
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
        if (!$this->_id) {
            $this->_storage->open();
        }
        $ret = (bool) $this->_storage->read($id);
        if (!$this->_id) {
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
        if (headers_sent() || $this->_id) {
            return false;
        }
        $this->data = array();
        if ($this->_requestedId) {
            $this->_set_cookie($this->_name, $this->_requestedId);
            $this->_id = $this->_requestedId;
            $this->_requestedId = null;
        } else {
            $id = $this->get_id_from_cookie();
            $this->_id = $id ? $id : IdGenerator::generateBase32Id($this->id_length);
        }
        
        // open storage (reqd for GC)
        $this->_storage->open();

        // should we call GC?
        $rand = mt_rand(1, $this->gc_divisor);
        if ($rand <= $this->gc_probability) {
            $this->_storage->gc($this->gc_maxlifetime);
        }

        // try data fetch
        if (! $this->_load_data()) {
            // unlike the native PHP session, we don't let users choose their own
            // session IDs if there's no data. This prevents session fixation through 
            // cookies (very hard for an attacker, but why leave this door open?).
            $this->_id = IdGenerator::generateBase32Id($this->id_length);
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
        if (!$this->_id || !$this->_save_data()) {
            return false;
        }
        $this->_storage->close();
        $this->_id = '';
        return true;
    }

    public function __destruct()
    {
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
        if (headers_sent() || !$this->_id) {
            return false;
        }
        $this->remove_cookie();
        $oldId = $this->_id;
        $this->_id = IdGenerator::generateBase32Id($this->id_length);
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
        $expire = $this->cookie_lifetime ? time() + (int) $this->cookie_lifetime : 0;
        return setcookie($name, $id, $expire, $this->cookie_path, $this->cookie_domain, (bool) $this->cookie_secure, (bool) $this->cookie_httponly);
    }

    /**
     * @var Util_UserlandSession_IStorage
     */
    protected $_storage;
    
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
    protected $_requestedId = '';
    
    /**
     * Copy of session name from storage handler
     * 
     * @var string
     */
    protected $_name;
}