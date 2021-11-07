<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Controller\Adminhtml\Category\Save as CategorySave;

class Save extends CategorySave implements HttpPostActionInterface
{
    /**
     * @var array
     */
    protected $stringToBoolInputs = [
        'custom_use_parent_settings',
        'custom_apply_to_products',
        'is_active',
        'include_in_menu',
        'is_anchor',
        'use_default' => ['url_key'],
        'use_config' => [
            'available_sort_by',
            'filter_price_range',
            'default_sort_by',
            'grid_per_page_values',
            'grid_per_page',
            'list_per_page_values',
            'list_per_page'
        ]
    ];
}
