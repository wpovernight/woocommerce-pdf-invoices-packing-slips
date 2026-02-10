<?php

namespace WPO\IPS\Vendor\Safe\Exceptions;

class OpensslException extends \Exception implements SafeExceptionInterface
{
    public static function createFromPhpError(): self
    {
        return new self(\openssl_error_string() ?: '', 0);
    }
}
