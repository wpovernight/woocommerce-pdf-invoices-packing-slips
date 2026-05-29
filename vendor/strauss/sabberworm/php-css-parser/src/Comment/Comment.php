<?php

declare(strict_types=1);

namespace WPO\IPS\Vendor\Sabberworm\CSS\Comment;

use WPO\IPS\Vendor\Sabberworm\CSS\OutputFormat;
use WPO\IPS\Vendor\Sabberworm\CSS\Position\Position;
use WPO\IPS\Vendor\Sabberworm\CSS\Position\Positionable;
use WPO\IPS\Vendor\Sabberworm\CSS\Renderable;
use WPO\IPS\Vendor\Sabberworm\CSS\ShortClassNameProvider;

class Comment implements Positionable, Renderable
{
    use Position;
    use ShortClassNameProvider;

    /**
     * @var string
     *
     * @internal since 8.8.0
     */
    protected $commentText;

    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(string $commentText = '', ?int $lineNumber = null)
    {
        $this->commentText = $commentText;
        $this->setPosition($lineNumber);
    }

    public function getComment(): string
    {
        return $this->commentText;
    }

    public function setComment(string $commentText): void
    {
        $this->commentText = $commentText;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        return '/*' . $this->commentText . '*/';
    }

    /**
     * @return array<string, bool|int|float|string|array<mixed>|null>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        return [
            'class' => $this->getShortClassName(),
            // "contents" is the term used in the W3C specs:
            // https://www.w3.org/TR/CSS22/syndata.html#comments
            'contents' => $this->commentText,
        ];
    }
}
