<?php

namespace Nx6\CloudFlare\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Fit implements OptionSourceInterface
{
    #[\Override]
    public function toOptionArray(): array
    {
        return [
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'scale-down', 'label' => 'Scale Down'],
            ['value' => 'contain', 'label' => 'Contain'],
            ['value' => 'cover', 'label' => 'Cover'],
            ['value' => 'crop', 'label' => 'Crop'],
            ['value' => 'pad', 'label' => 'Pad']
        ];
    }
}
