<?php

namespace Ex6\CloudFlare\Plugin\Template;

use Ex6\CloudFlare\Helper\Image as ImageHelper;
use Ex6\CloudFlare\Model\Config as CloudflareConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\Filter as TemplateFilter;

class FilterPlugin
{
    /**
     * FilterPlugin constructor.
     */
    public function __construct(protected CloudflareConfig $cloudflareConfig, protected ImageHelper $imageHelper)
    {
    }

    /**
     * @param $returnedUrl
     * @param $construction
     * @throws NoSuchEntityException
     */
    public function afterMediaDirective(TemplateFilter $templateFilter, $returnedUrl, $construction): string
    {
        if ($this->cloudflareConfig->isEnabled() && $this->cloudflareConfig->isEnabledForCms()) {
            return $this->imageHelper->getCmsImage($construction);
        }

        return $returnedUrl;
    }
}
