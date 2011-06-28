<?php

namespace Shibalike;

/**
 * The state manager is responsible for tying a User object and some other state
 * to a user, usually via a session.
 */
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
     * Forget all state data
     */
    public function forget();

    /**
     * Persist any session data to disk (e.g. called before a redirect)
     */
    public function writeClose();
    
    /**
     * @param string $key
     * @return string|null
     */
    public function getMetadata($key);
    
    /**
     * @param string $key
     * @param string $value if null, this key will be removed
     * @return bool
     */
    public function setMetadata($key, $value = null);
}
