<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Model;

use Magento\Catalog\Model\Category\DataProvider;

class CategoryDataProvider extends DataProvider
{
    /**
     * @var array
     */
    protected $elementsWithUseConfigSetting = [
        'available_sort_by',
        'default_sort_by',
        'filter_price_range',
        'grid_per_page_values',
        'grid_per_page',
        'list_per_page_values',
        'list_per_page'
    ];

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        $fieldMap = parent::getFieldsMap();
        $fieldMap['promo_banners'] = [
            'grid_per_page_values',
            'use_config.grid_per_page_values',
            'grid_per_page',
            'use_config.grid_per_page',
            'list_per_page_values',
            'use_config.list_per_page_values',
            'list_per_page',
            'use_config.list_per_page'
        ];
        return $fieldMap;
    }

    /**
     * @param array $result
     * @return array
     */
    public function getDefaultMetaData($result)
    {
        $result = parent::getDefaultMetaData($result);
        $result['use_config.grid_per_page_values']['default'] = true;
        $result['use_config.grid_per_page']['default'] = true;
        $result['use_config.list_per_page_values']['default'] = true;
        $result['use_config.list_per_page']['default'] = true;
        return $result;
    }
}
