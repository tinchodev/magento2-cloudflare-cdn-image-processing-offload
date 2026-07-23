<?php

namespace Ex6\CloudFlare\Model\Product;

use Ex6\CloudFlare\Helper\Image as ImageHelper;
use Ex6\CloudFlare\Model\Config as CloudFlareConfig;
use Magento\Catalog\Model\Product\Image as ParentImage;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\View\Asset\ImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\Factory as MagentoImageFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\FileSystem as MagentoFileSystem;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\StoreManagerInterface;

class Image extends ParentImage
{
    /**
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $db
     * @param SerializerInterface|null $serializer
     * @param ParamsBuilder|null $paramsBuilder
     * @throws FileSystemException
     */
    public function __construct(
        Context               $context,
        Registry              $registry,
        StoreManagerInterface $storeManager,
        Config                $catalogProductMediaConfig,
        Database              $coreFileStorageDatabase,
        Filesystem            $filesystem,
        MagentoImageFactory   $magentoImageFactory,
        Repository            $assetRepository,
        MagentoFileSystem     $magentoFileSystem,
        ImageFactory          $viewAssetImageFactory,
        PlaceholderFactory    $viewAssetPlaceholderFactory,
        ScopeConfigInterface  $scopeConfig,
        protected CloudFlareConfig      $cloudflareConfig,
        protected ImageHelper           $imageHelper,
        AbstractResource      $resource = null,
        AbstractDb            $db = null,
        array                 $data = [],
        SerializerInterface   $serializer = null,
        ParamsBuilder         $paramsBuilder = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $catalogProductMediaConfig,
            $coreFileStorageDatabase,
            $filesystem,
            $magentoImageFactory,
            $assetRepository,
            $magentoFileSystem,
            $viewAssetImageFactory,
            $viewAssetPlaceholderFactory,
            $scopeConfig,
            $resource,
            $db,
            $data,
            $serializer,
            $paramsBuilder
        );
    }

    /**
     * Offload image creation to CloudFlare
     * @return Image|$this
     */
    #[\Override]
    public function saveFile(): Image|static
    {
        if (!$this->cloudflareConfig->isEnabled()) {
            return parent::saveFile();
        }

        return $this;
    }

    /**
     * @throws NoSuchEntityException
     */
    #[\Override]
    public function getUrl(): string
    {
        if (!$this->cloudflareConfig->isEnabled()) {
            return parent::getUrl();
        }

        $params = [
            'image_width' => $this->getWidth(),
            'image_height' => $this->getHeight(),
        ];
        return $this->imageHelper->getProductImage($this->getBaseFile(), $params);
    }

}
