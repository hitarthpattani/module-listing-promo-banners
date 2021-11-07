<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Model\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    /**
     * @var string
     */
    const XML_PATH_LISTING_PROMO_BANNERS_ENABLED = 'catalog/listing_promo_banners/enabled';
    const XML_PATH_FRONTEND_GRID_PER_PAGE_VALUES = 'catalog/frontend/grid_per_page_values';
    const XML_PATH_FRONTEND_GRID_PER_PAGE = 'catalog/frontend/grid_per_page';
    const XML_PATH_FRONTEND_LIST_PER_PAGE_VALUES = 'catalog/frontend/list_per_page_values';
    const XML_PATH_FRONTEND_LIST_PER_PAGE = 'catalog/frontend/list_per_page';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $path
     * @param number $storeId
     * @return mixed
     */
    private function execute($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param number $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool) $this->execute(self::XML_PATH_LISTING_PROMO_BANNERS_ENABLED, $storeId);
    }

    /**
     * @param number $storeId
     * @return string|null
     */
    public function getGridPerPageValues($storeId = null)
    {
        return $this->execute(self::XML_PATH_FRONTEND_GRID_PER_PAGE_VALUES, $storeId);
    }

    /**
     * @param number $storeId
     * @return string|null
     */
    public function getGridPerPage($storeId = null)
    {
        return $this->execute(self::XML_PATH_FRONTEND_GRID_PER_PAGE, $storeId);
    }

    /**
     * @param number $storeId
     * @return string|null
     */
    public function getListPerPageValues($storeId = null)
    {
        return $this->execute(self::XML_PATH_FRONTEND_LIST_PER_PAGE_VALUES, $storeId);
    }

    /**
     * @param number $storeId
     * @return string|null
     */
    public function getListPerPage($storeId = null)
    {
        return $this->execute(self::XML_PATH_FRONTEND_LIST_PER_PAGE, $storeId);
    }
}
