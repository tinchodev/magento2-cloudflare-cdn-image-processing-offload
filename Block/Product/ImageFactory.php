<?php

namespace Ex6\CloudFlare\Block\Product;

use Ex6\CloudFlare\Helper\Image;
use Ex6\CloudFlare\Model\Config;
use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Block\Product\ImageFactory as ParentImageFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;

class ImageFactory extends ParentImageFactory
{
    public function __construct(
        protected ObjectManagerInterface $objectManager,
        protected ConfigInterface        $presentationConfig,
        protected AssetImageFactory      $viewAssetImageFactory,
        protected PlaceholderFactory     $viewAssetPlaceholderFactory,
        protected ParamsBuilder          $imageParamsBuilder,
        protected Config                 $config,
        protected Image                  $image
    ) {

        parent::__construct(
            $this->objectManager,
            $this->presentationConfig,
            $this->viewAssetImageFactory,
            $this->viewAssetPlaceholderFactory,
            $this->imageParamsBuilder
        );
    }

    /**
     * @param array|null $attributes
     * @throws LocalizedException
     */
    #[\Override]
    public function create(Product $product, string $imageId, array $attributes = null): ImageBlock
    {
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );

        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);

        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type']
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $originalFilePath,
                ]
            );
        }

        $attributes ??= [];

        $data = [
            'data' => [
                'template' => 'Ex6_CloudFlare::catalog/product/image_with_borders.phtml',
                'image_url' => $imageAsset->getUrl(),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'label' => $this->getLabel($product, $imageMiscParams['image_type'] ?? ''),
                'ratio' => $this->getRatio($imageMiscParams['image_width'] ?? 0, $imageMiscParams['image_height'] ?? 0),
                'custom_attributes' => $this->filterCustomAttributes($attributes),
                'class' => $this->getClass($attributes),
                'product_id' => $product->getId()
            ],
        ];

        if ($this->config->isEnabledForMobile('plp')) {
            $imageSrcSet = $this->image->getProductMobileImages(
                $originalFilePath
            );
            $imageSrcSet = implode(',' . PHP_EOL, $imageSrcSet);
            $data['data']['srcset'] = $imageSrcSet;
        }

        return $this->objectManager->create(ImageBlock::class, $data);
    }

    /**
     * Get image label
     */
    private function getLabel(Product $product, string $imageType): string
    {
        $label = $product->getData($imageType . '_' . 'label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return (string)$label;
    }

    private function getRatio(int $width, int $height): float
    {
        if ($width && $height) {
            return $height / $width;
        }

        return 1.0;
    }

    private function filterCustomAttributes(array $attributes): array
    {
        if (isset($attributes['class'])) {
            unset($attributes['class']);
        }

        return $attributes;
    }

    /**
     * Retrieve image class for HTML element
     */
    private function getClass(array $attributes): string
    {
        return $attributes['class'] ?? 'product-image-photo';
    }

}
