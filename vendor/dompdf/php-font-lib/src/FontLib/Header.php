<?php
/**
 * @package php-font-lib
 * @link    https://github.com/dompdf/php-font-lib
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * Modified by wpovernight on 18-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */
namespace WPO\IPS\Vendor\FontLib;

use WPO\IPS\Vendor\FontLib\TrueType\File;

/**
 * Font header container.
 *
 * @package php-font-lib
 */
abstract class Header extends BinaryStream {
  /**
   * @var File
   */
  protected $font;
  protected $def = array();

  public $data;

  public function __construct(File $font) {
    $this->font = $font;
  }

  public function encode() {
    return $this->font->pack($this->def, $this->data);
  }

  public function parse() {
    $this->data = $this->font->unpack($this->def);
  }
}