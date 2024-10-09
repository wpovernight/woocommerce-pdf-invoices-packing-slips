<?php
/**
 * @license MIT
 *
 * Modified by wpovernight on 30-July-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPO\IPS\Vendor\Sabberworm\CSS\Value;

use WPO\IPS\Vendor\Sabberworm\CSS\OutputFormat;

class CalcRuleValueList extends RuleValueList
{
    /**
     * @param int $iLineNo
     */
    public function __construct($iLineNo = 0)
    {
        parent::__construct(',', $iLineNo);
    }

    /**
     * @return string
     */
    public function render(OutputFormat $oOutputFormat)
    {
        return $oOutputFormat->implode(' ', $this->aComponents);
    }
}
