<?php

namespace Shibalike\Util;

class IdGenerator {

    /**
     * Create a random alphanumeric string
     * 
     * @param int $length
     * @return string 
     */
    public static function generateBase32Id($length = 40)
    {
        // generate random bytes, more than we need (adapted from phpass)
        $numBytes = $length;
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
                $randomState = md5(microtime() . $randomState . mt_rand(0, mt_getrandmax()));
                $bytes .= pack('H*', md5($randomState));
            }
            $bytes = substr($bytes, 0, $numBytes);
        }
        // convert bytes to base36, strip non-alphanumerics), return a random chunk
        return substr(self::_bin_to_base32($bytes), 0, $length);
    }

    /**
     * Convert binary input to base32 (lossy for speed purposes, not a reversible operation).
     * 
     * @link http://inquisitivecocoa.com/2009/08/10/a-basic-base32_encode-function-for-php/
     * @param string $bytes
     * @return string 
     * @license unknown
     */
    static protected function _bin_to_base32($bytes)
    {
        $hex = unpack('H*', $bytes);
        $hex = $hex[1];
        $binary = '';
        for ($i = 0, $l = strlen($hex); $i < $l; $i++) {
            $binary .= decbin(hexdec($hex[$i]));
        }

        $binaryLength = strlen($binary);
        $base32_characters = "0123456789abcdefghijklmnopqrstuv";
        $currentPosition = 0;
        $output = '';

        while ($currentPosition < $binaryLength) {
            $bits = substr($binary, $currentPosition, 5);
            // don't worry about padding last 5-bit number
            // Convert the 5 bits into a decimal number
            // and append the matching character to $output
            $output .= $base32_characters[bindec($bits)];
            $currentPosition += 5;
        }
        // don't bother padding
        return $output;
    }
}