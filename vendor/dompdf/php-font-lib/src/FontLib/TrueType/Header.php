<?php
/**
 * @package php-font-lib
 * @link    https://github.com/dompdf/php-font-lib
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * Modified by wpovernight on 18-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPO\IPS\Vendor\FontLib\TrueType;

/**
 * TrueType font file header.
 *
 * @package php-font-lib
 */
class Header extends \WPO\IPS\Vendor\FontLib\Header {
  protected $def = array(
    "format"        => self::uint32,
    "numTables"     => self::uint16,
    "searchRange"   => self::uint16,
    "entrySelector" => self::uint16,
    "rangeShift"    => self::uint16,
  );

  public function parse() {
    parent::parse();

    $format                   = $this->data["format"];
    $this->data["formatText"] = $this->convertUInt32ToStr($format);
  }
}