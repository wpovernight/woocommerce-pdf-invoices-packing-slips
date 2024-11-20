<?php
/**
 * @package dompdf
 * @link    https://github.com/dompdf/dompdf
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * Modified by wpovernight on 18-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */
namespace WPO\IPS\Vendor\Dompdf\Positioner;

use WPO\IPS\Vendor\Dompdf\Exception;
use WPO\IPS\Vendor\Dompdf\FrameDecorator\AbstractFrameDecorator;
use WPO\IPS\Vendor\Dompdf\FrameDecorator\Table;

/**
 * Positions table cells
 *
 * @package dompdf
 */
class TableCell extends AbstractPositioner
{

    /**
     * @param AbstractFrameDecorator $frame
     */
    function position(AbstractFrameDecorator $frame): void
    {
        $table = Table::find_parent_table($frame);
        if ($table === null) {
            throw new Exception("Parent table not found for table cell");
        }
        $cellmap = $table->get_cellmap();
        $frame->set_position($cellmap->get_frame_position($frame));
    }
}
