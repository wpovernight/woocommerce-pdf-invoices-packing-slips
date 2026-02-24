<?php

namespace WPO\IPS\Vendor\Safe;

use WPO\IPS\Vendor\Safe\Exceptions\FpmException;

/**
 * This function flushes all response data to the client and finishes the
 * request. This allows for time consuming tasks to be performed without
 * leaving the connection to the client open.
 *
 * @throws FpmException
 *
 */
function fastcgi_finish_request(): void
{
    error_clear_last();
    $safeResult = \fastcgi_finish_request();
    if ($safeResult === false) {
        throw FpmException::createFromPhpError();
    }
}
