<?php
/**
 * @license LGPL-2.1
 *
 * Modified by wpovernight on 15-July-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */
namespace WPO\IPS\Vendor\Dompdf\Css\Content;

abstract class ContentPart
{
    public function equals(self $other): bool
    {
        return $other instanceof static;
    }
}
