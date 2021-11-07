<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Plugin\Backend;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Category\DataProvider;
use HitarthPattani\ListingPromoBanners\Model\Helper\ConfigProvider;

class PagerAttributesCategoryDataProvider
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @param DataProvider $subject
     * @param array $data
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetData(
        DataProvider $subject,
        $data
    ) {
        $category = $subject->getCurrentCategory();
        $categoryId = $category->getId();

        if (!isset($data[$categoryId]['grid_per_page_values'])) {
            $data[$categoryId]['grid_per_page_values'] = $this->configProvider->getGridPerPageValues();
        }
        if (!isset($data[$categoryId]['grid_per_page'])) {
            $data[$categoryId]['grid_per_page'] = $this->configProvider->getGridPerPage();
        }
        if (!isset($data[$categoryId]['list_per_page_values'])) {
            $data[$categoryId]['list_per_page_values'] = $this->configProvider->getListPerPageValues();
        }
        if (!isset($data[$categoryId]['list_per_page'])) {
            $data[$categoryId]['list_per_page'] = $this->configProvider->getListPerPage();
        }

        return $data;
    }
}
