<?php
/**
 * @license LGPL-2.1
 *
 * Modified by wpovernight on 18-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */
namespace WPO\IPS\Vendor\Dompdf\Css\Content;

final class StringPart extends ContentPart
{
    /**
     * @var string
     */
    public $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function equals(ContentPart $other): bool
    {
        return $other instanceof self
            && $other->string === $this->string;
    }

    public function __toString(): string
    {
        return '"' . $this->string . '"';
    }
}
