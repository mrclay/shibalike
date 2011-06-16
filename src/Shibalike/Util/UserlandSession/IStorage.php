<?php

namespace Shibalike;

interface Util_UserlandSession_IStorage {
    
    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public function open($savePath = '', $sessionName = '');
    
    /**
     * @return bool
     */
    public function close();
    
    /**
     * @param string $id
     * @return bool
     */
    public function read($id);
    
    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data);
    
    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id);
    
    /**
     * @param int $maxLifetime
     * @return bool
     */
    public function gc($maxLifetime);
    
    /**
     * @param string $id
     * @return bool
     */
    public function idIsValid($id);
}