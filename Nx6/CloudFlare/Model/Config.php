<?php

namespace Nx6\CloudFlare\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_CONFIG_PATH_CLOUDFLARE = 'cloudflare/';

    const XML_CONFIG_PATH_CLOUDFLARE_GENERAL = self::XML_CONFIG_PATH_CLOUDFLARE . 'general/';

    const XML_CONFIG_PATH_CLOUDFLARE_IMAGE_OPTIMIZATION = self::XML_CONFIG_PATH_CLOUDFLARE . 'image_optimization/';

    const XML_CONFIG_PATH_CLOUDFLARE_MOBILE_IMAGES = self::XML_CONFIG_PATH_CLOUDFLARE . 'mobile/';

    const OPTIMIZATION_PARAM_LIST = [
        'compression',
        'quality',
        'format',
        'fit',
    ];

    public function __construct(protected ScopeConfigInterface $scopeConfig, protected EncryptorInterface $encryptor)
    {
    }

    public function getApiKey(): ?string
    {
        $apiKey = $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_CLOUDFLARE_GENERAL . 'api_key',
            ScopeInterface::SCOPE_STORE,
        );
        return $apiKey ? $this->encryptor->decrypt($apiKey) : null;
    }

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_CLOUDFLARE_IMAGE_OPTIMIZATION . 'enabled',
            ScopeInterface::SCOPE_STORE,
        );
    }

    public function isEnabledForCms(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_CLOUDFLARE_IMAGE_OPTIMIZATION . 'cms_enabled',
            ScopeInterface::SCOPE_STORE,
        );
    }

    public function getOptimizationParams(): array
    {
        $params = [];
        foreach (self::OPTIMIZATION_PARAM_LIST as $setting) {
            $value = $this->scopeConfig->getValue(
                self::XML_CONFIG_PATH_CLOUDFLARE_IMAGE_OPTIMIZATION . $setting,
                ScopeInterface::SCOPE_STORE,
            );
            if ($value && $value !== 'none') {
                $params[$setting] = $value;
            }
        }

        return $params;
    }

    public function isEnabledForMobile(string $type): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_CLOUDFLARE_MOBILE_IMAGES . $type,
            ScopeInterface::SCOPE_STORE,
        );
    }

    public function getMobileSizes(string $type): array
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_CLOUDFLARE_MOBILE_IMAGES . $type . '_widths',
            ScopeInterface::SCOPE_STORE,
        );
        $value = preg_replace('/\s+/', '', $value);
        return array_map('intval', explode(',', (string) $value));
    }

}
