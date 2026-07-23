<?php

namespace Ex6\CloudFlare\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Compression implements OptionSourceInterface
{
    #[\Override]
    public function toOptionArray(): array
    {
        return [
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'fast', 'label' => 'Fast']
        ];
    }
}
