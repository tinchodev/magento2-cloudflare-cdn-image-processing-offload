<?php

namespace Nx6\CloudFlare\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Format implements OptionSourceInterface
{
    #[\Override]
    public function toOptionArray(): array
    {
        return [
            ['value' => 'auto', 'label' => 'None'],
            ['value' => 'avif', 'label' => 'AVIF'],
            ['value' => 'webp', 'label' => 'WEBP'],
            ['value' => 'jpeg', 'label' => 'JPEG'],
            ['value' => 'baseline-jpeg', 'label' => 'Baseline JPEG'],
        ];
    }
}
