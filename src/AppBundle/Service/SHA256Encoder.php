<?php

namespace AppBundle\Service;

class SHA256Encoder
{
    public function encode($string, $salt = "")
    {
        return hash('sha256', $salt . $string . $salt);
    }

    public function generateSalt(int $length)
    {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.,:;!@#$%^&*)(_+="), 0, $length);
    }
}