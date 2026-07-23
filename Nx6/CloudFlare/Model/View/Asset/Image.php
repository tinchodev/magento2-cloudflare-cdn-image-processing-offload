<?php

namespace Nx6\CloudFlare\Model\View\Asset;

use Nx6\CloudFlare\Helper\Image as CloudFlareImageHelper;
use Nx6\CloudFlare\Model\Config as CloudFlareConfig;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Catalog\Model\View\Asset\Image as ParentImage;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class Image extends ParentImage
{
    public function __construct(
        ConfigInterface       $mediaConfig,
        ContextInterface      $context,
        EncryptorInterface    $encryptor,
        protected CloudFlareConfig      $cloudflareConfig,
        protected CloudFlareImageHelper $cloudflareImageHelper,
        $filePath,
        protected array                 $miscParams,
        ImageHelper           $imageHelper = null,
        CatalogMediaConfig    $catalogMediaConfig = null,
        StoreManagerInterface $storeManager = null
    ) {
        parent::__construct(
            $mediaConfig,
            $context,
            $encryptor,
            $filePath,
            $this->miscParams,
            $imageHelper,
            $catalogMediaConfig,
            $storeManager
        );
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    #[\Override]
    public function getUrl(): string
    {
        if (!$this->cloudflareConfig->isEnabled()) {
            return parent::getUrl();
        }

        return $this->cloudflareImageHelper->getProductImage($this->getFilePath(), $this->miscParams);
    }

}
