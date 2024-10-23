<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/dompdf/php-svg-lib
 * @license GNU LGPLv3+ http://www.gnu.org/copyleft/lesser.html
 *
 * Modified by wpovernight on 18-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPO\IPS\Vendor\Svg\Tag;

use WPO\IPS\Vendor\Sabberworm\CSS;

class StyleTag extends AbstractTag
{
    protected $text = "";

    public function end()
    {
        $parser = new CSS\Parser($this->text);
        $this->document->appendStyleSheet($parser->parse());
    }

    public function appendText($text)
    {
        $this->text .= $text;
    }
} 
