<?php

namespace Shibalike;

interface IStateManager {
    /**
     * @return bool was the user set successfully?
     * @param User $user
     */
    public function setUser(User $user);

    /**
     * @return User|null
     */
    public function getUser();

    /**
     * @return bool was the user unset successfully?
     */
    public function unsetUser();

    /**
     * @param string $url
     */
    public function setReturnUrl($url);

    /**
     * @return string
     */
    public function getReturnUrl();

    /**
     * Forget all state data
     */
    public function forget();
    
    /**
     * Persist any session data to disk (e.g. called before a redirect)
     */
    public function writeClose();
}
