<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Plugin\Frontend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\ScopeInterface;
use HitarthPattani\ListingPromoBanners\Model\Helper\ConfigProvider;

class PagerConfigProductList
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Category
     */
    private $currentCategory = null;

    /**
     * @var array
     */
    private $defaultAvailableLimit = [10 => 10, 20 => 20, 50 => 50];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $coreRegistry
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $coreRegistry,
        ConfigProvider $configProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
        $this->configProvider = $configProvider;
    }

    /**
     * @param ProductList $subject
     * @param array $result
     * @param string $mode
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableLimit(
        ProductList $subject,
        $result,
        $mode
    ) {
        if ($this->configProvider->isEnabled()) {
            if (!in_array($mode, [ProductList::VIEW_MODE_GRID, ProductList::VIEW_MODE_LIST])) {
                return $this->defaultAvailableLimit;
            }

            $perPageValues = $this->getCurrentCategory()->getData($mode . '_per_page_values');
            if ($perPageValues) {
                $perPageValues = explode(',', $perPageValues);
                $perPageValues = array_combine($perPageValues, $perPageValues);
                if ($this->scopeConfig->isSetFlag(
                    'catalog/frontend/list_allow_all',
                    ScopeInterface::SCOPE_STORE
                )) {
                    $result = ($perPageValues + ['all' => __('All')]);
                } else {
                    $result = $perPageValues;
                }
            }
        }

        return $result;
    }

    /**
     * @param ProductList $subject
     * @param array $result
     * @param string $mode
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetDefaultLimitPerPageValue(
        ProductList $subject,
        $result,
        $mode
    ) {
        if ($this->configProvider->isEnabled()) {
            $defaultPerPageValue = $this->getCurrentCategory()->getData($mode . '_per_page');
            if ($defaultPerPageValue) {
                $result = $defaultPerPageValue;
            }
        }

        return $result;
    }

    /**
     * Get default sort field
     *
     * @return null|string
     */
    private function getCurrentCategory()
    {
        if ($this->currentCategory == null) {
            $this->currentCategory = $this->coreRegistry->registry('current_category');
        }

        return $this->currentCategory;
    }
}
