<?php

if (!function_exists('hash_password_bcrypt')) {
    /**
     * Hash password using bcrypt with cost 11
     *
     * @param string $password
     * @return string
     */
    function hash_password_bcrypt(string $password): string
    {
        $options = [
            'cost' => 11,
        ];
        
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
}

if (!function_exists('verify_password_bcrypt')) {
    /**
     * Verify password against hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    function verify_password_bcrypt(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}





