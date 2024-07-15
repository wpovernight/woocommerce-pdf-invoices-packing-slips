<?php
/**
 * @license MIT
 *
 * Modified by wpovernight on 15-July-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPO\IPS\Vendor\Sabberworm\CSS;

interface Renderable
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function render(OutputFormat $oOutputFormat);

    /**
     * @return int
     */
    public function getLineNo();
}
