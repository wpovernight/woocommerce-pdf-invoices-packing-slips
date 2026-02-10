<?php

declare(strict_types=1);

namespace WPO\IPS\Vendor\Sabberworm\CSS;

interface Renderable
{
    public function render(OutputFormat $outputFormat): string;
}
