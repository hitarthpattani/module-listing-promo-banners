<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\ViewModel;

use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\Category;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use HitarthPattani\ListingPromoBanners\Observer\CategorySavePromoBanners;
use Zend_Filter_Exception;
use Zend_Filter_Interface;

class PromoBanners implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var Zend_Filter_Interface
     */
    private $templateProcessor;

    /**
     * @param Registry $coreRegistry
     * @param BlockRepositoryInterface $blockRepository
     * @param Zend_Filter_Interface $templateProcessor
     */
    public function __construct(
        Registry $coreRegistry,
        BlockRepositoryInterface $blockRepository,
        Zend_Filter_Interface $templateProcessor
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->blockRepository = $blockRepository;
        $this->templateProcessor = $templateProcessor;
    }

    /**
     * Get category in-grid banners
     *
     * @return array
     */
    public function getPromoBanners()
    {
        $loadedData = [];

        $promoBanners = $this->getCurrentCategory()->getPromoBanners();

        if (isset($promoBanners)) {
            $promoBanners = json_decode($promoBanners, true);

            $positions = array_map(
                function ($element) {
                    return $element['position'];
                },
                $promoBanners
            );

            foreach ($promoBanners as $block) {
                $position = 0;

                if (isset($block['position']) && $block['position'] !== "" && $block['position'] !== "0") {
                    $position = (int)($block['position']);
                }

                if ($position === 0 || array_key_exists($position, $loadedData)) {
                    $position = $this->getPosition($loadedData, min($positions));
                }

                // phpcs:disable Generic.Files.LineLength.TooLong
                $loadedData[$position] = [
                    BlockInterface::BLOCK_ID => $block[BlockInterface::BLOCK_ID],
                    CategorySavePromoBanners::PROMO_BANNERS_STYLE => $block[CategorySavePromoBanners::PROMO_BANNERS_STYLE]
                ];
                // phpcs:enable
            }
        }

        return $loadedData;
    }

    /**
     * @param $blockData
     * @return mixed|string
     * @throws LocalizedException
     * @throws Zend_Filter_Exception
     */
    public function filterOutputHtml($blockData)
    {
        if (isset($blockData[BlockInterface::BLOCK_ID])) {
            return $this->getBlockHtml($blockData[BlockInterface::BLOCK_ID]);
        }

        return '';
    }

    /**
     * Get current category object
     *
     * @return Category
     */
    private function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * @param $blockId
     * @return mixed|string
     * @throws LocalizedException
     * @throws Zend_Filter_Exception
     */
    private function getBlockHtml($blockId)
    {
        $block = $this->blockRepository->getById($blockId);

        if ($block->getId()) {
            return $this->templateProcessor->filter($block->getContent());
        }

        return '';
    }

    /**
     * @param array $loadedData
     * @param int $position
     * @return int
     */
    private function getPosition(array $loadedData, int $position)
    {
        if ($position == 0) {
            $position = 1;
        }

        while (array_key_exists($position, $loadedData)) {
            $position++;
        }

        return $position;
    }
}
