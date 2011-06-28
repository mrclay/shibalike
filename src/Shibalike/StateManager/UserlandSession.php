<?php

namespace Shibalike\StateManager;

use Shibalike\IStateManager;
use Shibalike\User;
use Shibalike\Util\UserlandSession as Sess;

class UserlandSession implements IStateManager {

    /**
     * @var \Shibalike\Util\UserlandSession
     */
    protected $_session;

    /**
     * @param \Shibalike\Util\UserlandSession $session 
     */
    public function __construct(Sess $session)
    {
        $this->_session = $session;
        $this->_session->start();
    }

    public function setUser(User $user)
    {
        $this->_session->data['shibalikeUser'] = $user;
    }

    public function getUser()
    {
        if (isset($this->_session->data['shibalikeUser'])) {
            return $this->_session->data['shibalikeUser'];
        }
        return null;
    }

    public function unsetUser()
    {
        unset($this->_session->data['shibalikeUser']);
        return true;
    }

    public function forget()
    {
        $this->_session->destroy(true);
    }

    public function writeClose()
    {
        $this->_session->write_close();
    }
    
    /**
     * @param string $key
     * @return string|null
     */
    public function getMetadata($key)
    {
        $key = 'shibalikeMeta_' . $key;
        return isset($this->_session->data[$key])
            ? $this->_session->data[$key]
            : null;
    }
    
    /**
     * @param string $key
     * @param string $value if null, this key will be removed
     * @return bool
     */
    public function setMetadata($key, $value = null)
    {
        $key = 'shibalikeMeta_' . $key;
        if ($value === null) {
            unset($this->_session->data[$key]);
        } else {
            $this->_session->data[$key] = $value;
        }
        return true;
    }
}
