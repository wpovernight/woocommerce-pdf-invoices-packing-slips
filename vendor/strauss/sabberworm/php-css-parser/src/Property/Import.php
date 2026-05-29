<?php

declare(strict_types=1);

namespace WPO\IPS\Vendor\Sabberworm\CSS\Property;

use WPO\IPS\Vendor\Sabberworm\CSS\Comment\CommentContainer;
use WPO\IPS\Vendor\Sabberworm\CSS\OutputFormat;
use WPO\IPS\Vendor\Sabberworm\CSS\Position\Position;
use WPO\IPS\Vendor\Sabberworm\CSS\Position\Positionable;
use WPO\IPS\Vendor\Sabberworm\CSS\ShortClassNameProvider;
use WPO\IPS\Vendor\Sabberworm\CSS\Value\URL;

/**
 * Class representing an `@import` rule.
 */
class Import implements AtRule, Positionable
{
    use CommentContainer;
    use Position;
    use ShortClassNameProvider;

    /**
     * @var URL
     */
    private $location;

    /**
     * @var string|null
     */
    private $mediaQuery;

    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(URL $location, ?string $mediaQuery, ?int $lineNumber = null)
    {
        $this->location = $location;
        $this->mediaQuery = $mediaQuery;
        $this->setPosition($lineNumber);
    }

    public function setLocation(URL $location): void
    {
        $this->location = $location;
    }

    public function getLocation(): URL
    {
        return $this->location;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        return $outputFormat->getFormatter()->comments($this) . '@import ' . $this->location->render($outputFormat)
            . ($this->mediaQuery === null ? '' : ' ' . $this->mediaQuery) . ';';
    }

    /**
     * @return non-empty-string
     */
    public function atRuleName(): string
    {
        return 'import';
    }

    /**
     * @return array{0: URL, 1?: non-empty-string}
     */
    public function atRuleArgs(): array
    {
        $result = [$this->location];
        if (\is_string($this->mediaQuery) && $this->mediaQuery !== '') {
            $result[] = $this->mediaQuery;
        }

        return $result;
    }

    public function getMediaQuery(): ?string
    {
        return $this->mediaQuery;
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
            // We're using the term "uri" here to match the wording used in the specs:
            // https://www.w3.org/TR/CSS22/cascade.html#at-import
            'uri' => $this->location->getArrayRepresentation(),
        ];
    }
}
