<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by wpovernight on 18-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPO\IPS\Vendor\Sabre\Uri;

/**
 * Invalid Uri.
 *
 * This is thrown when an attempt was made to use Sabre\Uri parse a uri that
 * it could not.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
class InvalidUriException extends \Exception
{
}
