<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by wpovernight on 18-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace WPO\IPS\Vendor\Sabre\Xml;

/**
 * This is the XML element interface.
 *
 * Elements are responsible for serializing and deserializing part of an XML
 * document into PHP values.
 *
 * It combines XmlSerializable and XmlDeserializable into one logical class
 * that does both.
 *
 * @copyright Copyright (C) 2009-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
interface Element extends XmlSerializable, XmlDeserializable
{
}
