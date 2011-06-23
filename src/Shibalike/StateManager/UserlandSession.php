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

    public function setReturnUrl($url)
    {
        $this->_session->data['returnUrl'] = $url;
    }

    public function getReturnUrl()
    {
        if (isset($this->_session->data['returnUrl'])) {
            return $this->_session->data['returnUrl'];
        }
        return null;
    }

    public function forget()
    {
        $this->_session->destroy(true);
    }

    public function writeClose()
    {
        $this->_session->write_close();
    }
}
