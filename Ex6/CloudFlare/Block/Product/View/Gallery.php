<?php

namespace Ex6\CloudFlare\Block\Product\View;

use Ex6\CloudFlare\Helper\Image as ImageHelper;
use Ex6\CloudFlare\Model\Config;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\Gallery as ParentGallery;
use Magento\Catalog\Helper\Image as GalleryImageHelper;
use Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Stdlib\ArrayUtils;

class Gallery extends ParentGallery
{
    protected $jsonEncoder;

    protected ImagesConfigFactoryInterface $galleryImagesConfigFactory;

    protected array $galleryImagesConfig;

    protected UrlBuilder $imageUrlBuilder;

    public function __construct(
        Context                      $context,
        ArrayUtils                   $arrayUtils,
        EncoderInterface             $jsonEncoder,
        protected ImageHelper                  $cloudflareImageHelper,
        protected GalleryImageHelper           $galleryImageHelper,
        protected Config                       $config,
        array                        $data = [],
        ImagesConfigFactoryInterface $imagesConfigFactory = null,
        array                        $galleryImagesConfig = [],
        UrlBuilder                   $urlBuilder = null
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $data,
            $imagesConfigFactory,
            $galleryImagesConfig,
            $urlBuilder
        );
        $this->jsonEncoder = $jsonEncoder;
        $this->galleryImagesConfigFactory = $imagesConfigFactory ?: ObjectManager::getInstance()
            ->get(ImagesConfigFactoryInterface::class);
        $this->galleryImagesConfig = $galleryImagesConfig;
        $this->imageUrlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
    }

    /**
     * @throws NoSuchEntityException
     */
    #[\Override]
    public function getGalleryImages(): Collection
    {
        $product = $this->getProduct();
        $collection = $product->getMediaGalleryImages();
        if (!$collection instanceof Collection) {
            return $collection;
        }

        foreach ($collection as $image) {
            $galleryImagesConfig = $this->getGalleryImagesConfig()->getItems();
            foreach ($galleryImagesConfig as $galleryImageConfig) {
                $image->setData(
                    $galleryImageConfig->getData('data_object_key'),
                    $this->imageUrlBuilder->getUrl($image->getFile(), $galleryImageConfig['image_id'])
                );
            }
        }

        if ($this->config->isEnabledForMobile('pdp')) {
            foreach ($collection->getItems() as $dataObject) {
                if ($dataObject->getData('media_type') == 'image') {
                    $dataObject->setData(
                        'srcset',
                        $this->cloudflareImageHelper->getProductMobileImages($dataObject->getData('file'), 'pdp')
                    );
                }
            }
        }

        return $collection;
    }

    /**
     * @throws NoSuchEntityException
     */
    #[\Override]
    public function getGalleryImagesJson(): string
    {
        $imagesItems = [];
        /** @var DataObject $image */
        foreach ($this->getGalleryImages() as $galleryImage) {
            $mediaType = $galleryImage->getMediaType();
            $imageItem = new DataObject(
                [
                    'thumb' => $galleryImage->getData('small_image_url'),
                    'img' => $galleryImage->getData('medium_image_url'),
                    'full' => $galleryImage->getData('large_image_url'),
                    'caption' => $galleryImage->getLabel() ?: $this->getProduct()->getName(),
                    'position' => $galleryImage->getData('position'),
                    'isMain' => $this->isMainImage($galleryImage),
                    'type' => $mediaType !== null ? str_replace('external-', '', $mediaType) : '',
                    'videoUrl' => $galleryImage->getVideoUrl(),
                    'srcset' => null,
                ]
            );

            if ($mediaType == 'image') {
                $imageItem->setData('srcset', $galleryImage->getData('srcset'));
            }

            foreach ($this->getGalleryImagesConfig()->getItems() as $imageConfig) {
                $imageItem->setData(
                    $imageConfig->getData('json_object_key'),
                    $galleryImage->getData($imageConfig->getData('data_object_key'))
                );
            }

            $imagesItems[] = $imageItem->toArray();
        }

        if ($imagesItems === []) {
            $imagesItems[] = [
                'thumb' => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
                'type' => 'image',
                'videoUrl' => null,
                'srcset' => null,
            ];
        }

        return json_encode($imagesItems);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getAllImagesJsonData(): string
    {
        $imagesItems = [];
        $product = $this->getProduct();
        foreach ($product->getMediaGalleryImages() as $mediaGalleryImage) {
            $mediaType = $mediaGalleryImage->getMediaType();
            if ($mediaType == 'image') {
                try {
                    $imageItem = new DataObject(
                        [
                            'img' => $mediaGalleryImage->getData('medium_image_url'),
                            'srcset' => $this->cloudflareImageHelper->getProductMobileImages($mediaGalleryImage->getData('file'), 'pdp'),
                        ]
                    );
                    $imagesItems[] = $imageItem->toArray();
                } catch (NoSuchEntityException) {

                }
            }
        }

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $child) {
                foreach ($child->getMediaGalleryImages() as $mediaGalleryImage) {
                    $mediaType = $mediaGalleryImage->getMediaType();
                    if ($mediaType == 'image') {
                        try {
                            $imageItem = new DataObject(
                                [
                                    'img' => $mediaGalleryImage->getData('medium_image_url') ?? $this->galleryImageHelper->init($child, 'product_page_image_medium')
                                            ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                                            ->setImageFile($mediaGalleryImage->getFile())
                                            ->getUrl(),
                                    'srcset' => $this->cloudflareImageHelper->getProductMobileImages($mediaGalleryImage->getData('file'), 'pdp'),
                                ]
                            );
                            $imagesItems[] = $imageItem->toArray();

                        } catch (NoSuchEntityException) {

                        }
                    }
                }
            }
        }

        return json_encode($imagesItems);
    }

    /**
     * Returns image gallery config object
     */
    private function getGalleryImagesConfig(): Collection
    {
        if (false === $this->hasData('gallery_images_config')) {
            $galleryImageConfig = $this->galleryImagesConfigFactory->create($this->galleryImagesConfig);
            $this->setData('gallery_images_config', $galleryImageConfig);
        }

        return $this->getData('gallery_images_config');
    }

    public function optimizationEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @param $type
     */
    public function optimizationEnabledForMobile(string $type): bool
    {
        return $this->config->isEnabled() && $this->config->isEnabledForMobile($type);
    }
}
