<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AddPagerAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply()
    {
        /** @var CategorySetup $eavSetup */
        $eavSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $entityTypeId = $eavSetup->getEntityTypeId(Category::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);

        $attributes = [
            [
                'attribute' => 'grid_per_page_values',
                'label' => 'Products per Page on Grid Allowed Values',
                'sort_order' => 10,
                'note' => 'Comma-separated.'
            ], [
                'attribute' => 'grid_per_page',
                'label' => 'Products per Page on Grid Default Value',
                'sort_order' => 20,
                'note' => 'Must be in the allowed values list'
            ], [
                'attribute' => 'list_per_page_values',
                'label' => 'Products per Page on List Allowed Values',
                'sort_order' => 30,
                'note' => 'Comma-separated.'
            ], [
                'attribute' => 'list_per_page',
                'label' => 'Products per Page on List Default Value',
                'sort_order' => 40,
                'note' => 'Must be in the allowed values list'
            ]
        ];

        foreach ($attributes as $attribute) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                $attribute['attribute'],
                [
                    'type' => 'text',
                    'label' => $attribute['label'],
                    'input' => 'text',
                    'source' => '',
                    'note' => $attribute['note'],
                    'required' => false,
                    'sort_order' => $attribute['sort_order'],
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Promo Banners',
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false
                ]
            );

            /** Add attribute to category group */
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'promo-banners'),
                $attribute['attribute'],
                $attribute['sort_order']
            );
        }
    }
}
