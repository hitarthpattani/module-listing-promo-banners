<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Model\Category;
use Magento\Cms\Api\Data\BlockInterface;
use HitarthPattani\ListingPromoBanners\Model\Helper\ConfigProvider;

class CategorySavePromoBanners implements ObserverInterface
{
    /**
     * @var string
     */
    const PROMO_BANNERS_POSITION = 'position';
    const PROMO_BANNERS_STYLE = 'style';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param SerializerInterface $serializer
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        SerializerInterface $serializer,
        ConfigProvider $configProvider
    ) {
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Observer $observer
     * @return CategorySavePromoBanners
     */
    public function execute(Observer $observer)
    {
        if (!$this->configProvider->isEnabled()) {
            return $this;
        }

        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $postData = $observer->getEvent()->getRequest()->getPostValue();

        if (!isset($postData['promo_banners']['data']['promo_banners'])) {
            return $category->setPromoBanners($this->serializer->serialize([]));
        }

        $promoBannerData = $postData['promo_banners']['data']['promo_banners'];

        $cmsBlocks = $promoBannerData['cms_block'] ?? [];

        $promoBanners = [];
        foreach ($cmsBlocks as $cmsBlock) {
            $promoBanners[] = [
                BlockInterface::BLOCK_ID => $cmsBlock['id'],
                BlockInterface::TITLE => $cmsBlock['title'],
                BlockInterface::IDENTIFIER => $cmsBlock['identifier'],
                self::PROMO_BANNERS_STYLE => $cmsBlock['style'],
                self::PROMO_BANNERS_POSITION => $cmsBlock['position'] ?? 1
            ];
        }

        $category->setPromoBanners($this->serializer->serialize($promoBanners));

        return $this;
    }
}
