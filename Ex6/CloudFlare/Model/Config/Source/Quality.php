<?php

namespace Ex6\CloudFlare\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Quality implements OptionSourceInterface
{
    #[\Override]
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => 'Original'],
            ['value' => 50, 'label' => '50'],
            ['value' => 55, 'label' => '55'],
            ['value' => 60, 'label' => '60'],
            ['value' => 65, 'label' => '65'],
            ['value' => 70, 'label' => '70'],
            ['value' => 75, 'label' => '75'],
            ['value' => 80, 'label' => '80'],
            ['value' => 85, 'label' => '85'],
            ['value' => 90, 'label' => '90'],
            ['value' => 95, 'label' => '95'],
        ];
    }
}
