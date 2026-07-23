<?php

namespace Ex6\CloudFlare\Helper;

use Ex6\CloudFlare\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Image
{
    const CDN_PATH = 'cdn-cgi/image/';

    const PRODUCT_PATH = 'catalog/product';

    public function __construct(protected Config $config, protected StoreManagerInterface $storeManager)
    {
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getProductImage(string $path, array $sourceParameters): string
    {
        $imageUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB) . self::CDN_PATH;
        $paramsData = [];
        $path = str_replace(self::PRODUCT_PATH, '', $path);
        foreach ($this->config->getOptimizationParams() as $param => $value) {
            $paramsData[] = $param . '=' . $value;
        }

        if (!empty($sourceParameters['image_width'])) {
            $paramsData[] = 'width=' . $sourceParameters['image_width'];
        }

        if (!empty($sourceParameters['image_height'])) {
            $paramsData[] = 'height=' . $sourceParameters['image_height'];
        }

        $paramsString = implode(',', $paramsData);
        return $this->buildUrl($imageUrl, $paramsString, self::PRODUCT_PATH . $path);

    }

    /**
     * @throws NoSuchEntityException
     */
    public function getProductMobileImages(string $path, string $type = 'plp'): array
    {
        $mobileImages = [];
        if ($this->config->isEnabledForMobile($type) && $this->config->isEnabledForMobile($type)) {
            $imageUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB) . self::CDN_PATH;
            $paramsData = [];
            $path = str_replace(self::PRODUCT_PATH, '', $path);
            foreach ($this->config->getOptimizationParams() as $param => $value) {
                if ($param != 'fit') { // fit will be enforced to "contain"
                    $paramsData[] = $param . '=' . $value;
                }
            }

            foreach ($this->config->getMobileSizes($type) as $size) {
                $paramsData['width'] = 'width=' . $size;
                $paramsData['fit'] = 'fit=contain';
                $paramsString = implode(',', $paramsData);
                $mobileImages[] = $this->buildUrl($imageUrl, $paramsString, self::PRODUCT_PATH . $path, $size);
            }
        }

        return $mobileImages;

    }

    /**
     * @throws NoSuchEntityException
     */
    public function getCmsImage(array $params): string
    {
        $imageUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB) . self::CDN_PATH;
        $paramsData = [];
        foreach ($this->config->getOptimizationParams() as $param => $value) {
            $paramsData[] = $param . '=' . $value;
        }

        $paramsString = implode(',', $paramsData);
        preg_match('/"?(wysiwyg.*)"?/', (string) $params[2], $paramsProcessed);
        $path = $paramsProcessed[1];
        return $this->buildUrl($imageUrl, $paramsString, $path);
    }

    protected function buildUrl(string $imageUrl, string $paramsString, string $path, int $mobileSize = 0): string
    {
        $image = $imageUrl .
            $paramsString .
            DIRECTORY_SEPARATOR .
            UrlInterface::URL_TYPE_MEDIA .
            DIRECTORY_SEPARATOR .
            $path;
        if ($mobileSize !== 0) {
            return $image . ' ' . $mobileSize . 'w';
        }

        return $image;
    }

}
