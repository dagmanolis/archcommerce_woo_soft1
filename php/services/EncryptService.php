<?php

namespace webxl\archcommerce\services;

class EncryptService
{

    function encrypt($string)
    {
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', ARCHCOMMERCE_SECRET_KEY);
        $iv = substr(hash('sha256', ARCHCOMMERCE_SECRET_IV), 0, 16);
        return base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
    }
    function decrypt($string)
    {
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', ARCHCOMMERCE_SECRET_KEY);
        $iv = substr(hash('sha256', ARCHCOMMERCE_SECRET_IV), 0, 16);
        return openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
}
