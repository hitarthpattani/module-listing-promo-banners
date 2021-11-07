<?php
/**
 * @package     HitarthPattani\ListingPromoBanners
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2021. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\ListingPromoBanners\Plugin\Backend;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Model\Category\DataProvider;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;

class PromoBannersCategoryDataProvider
{
    /**
     * @var string
     */
    const SCOPE_NAME = 'category_form.category_form';

    /**
     * @var string
     */
    const DATA_SCOPE_PROMO_BANNERS = 'promo_banners';
    const DATA_GROUP_PROMO_BANNERS = 'promo_banners';

    /**
     * @var string
     */
    const DATA_SCOPE_CMS_BLOCK = 'cms_block';

    /**
     * @var string
     */
    const ATTRIBUTE_CODE = 'promo_banners';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $scopePrefix;

    /**
     * @var EavAttribute
     */
    private $attribute = null;

    /**
     * @param SerializerInterface $serializer
     * @param UrlInterface $urlBuilder
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param string $scopePrefix
     */
    public function __construct(
        SerializerInterface $serializer,
        UrlInterface $urlBuilder,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        $scopePrefix = ''
    ) {
        $this->serializer = $serializer;
        $this->urlBuilder = $urlBuilder;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->scopePrefix = $scopePrefix;
    }

    /**
     * @return EavAttribute|AbstractAttribute
     * @throws LocalizedException
     */
    private function getAttribute()
    {
        if ($this->attribute === null) {
            $this->attribute = $this->eavConfig->getAttribute(
                CategoryAttributeInterface::ENTITY_TYPE_CODE,
                self::ATTRIBUTE_CODE
            );
        }

        return $this->attribute;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @param EavAttribute $attribute
     * @return string
     * @since 101.0.0
     */
    public function getScopeLabel(EavAttribute $attribute)
    {
        $html = '';
        if (!$attribute || $this->storeManager->isSingleStoreMode()
            || $attribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return $html;
        }
        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }

        return $html;
    }

    /**
     * @param DataProvider $subject
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMeta(
        DataProvider $subject,
        array $meta
    ) {
        $meta = array_merge_recursive(
            $meta,
            [
                self::DATA_GROUP_PROMO_BANNERS => [
                    'children' => [
                        $this->scopePrefix . self::DATA_SCOPE_CMS_BLOCK => $this->getPromoBannersFieldset()
                    ]
                ],
            ]
        );

        return $meta;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getPromoBannersFieldset()
    {
        $content = __(
            'Add CMS blocks to display as promo banners on the category product listing.'
        );

        $attribute = $this->getAttribute();

        return [
            'children' => [
                'button_set' => $this->getButtonSet(
                    $content,
                    __('Add Promo Banners'),
                    $this->scopePrefix . self::DATA_SCOPE_CMS_BLOCK
                ),
                'modal' => $this->getGenericModal(
                    __('Add Promo Banners'),
                    $this->scopePrefix . self::DATA_SCOPE_CMS_BLOCK
                ),
                self::DATA_SCOPE_CMS_BLOCK => $this->getGrid($this->scopePrefix . self::DATA_SCOPE_CMS_BLOCK),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Promo Banners %1', $this->getScopeLabel($attribute)),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => self::DATA_SCOPE_PROMO_BANNERS,
                        'sortOrder' => 100
                    ],
                ],
            ]
        ];
    }

    /**
     * @param Phrase $content
     * @param Phrase $buttonTitle
     * @param $scope
     * @return array
     */
    protected function getButtonSet(Phrase $content, Phrase $buttonTitle, $scope)
    {
        $modalTarget = self::SCOPE_NAME . '.' . self::DATA_GROUP_PROMO_BANNERS . '.' . $scope . '.modal';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => $content,
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'button_' . $scope => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $modalTarget,
                                        'actionName' => 'toggleModal',
                                    ],
                                    [
                                        'targetName' => $modalTarget . '.' . $scope . '_listing',
                                        'actionName' => 'render',
                                    ]
                                ],
                                'title' => $buttonTitle,
                                'provider' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param Phrase $title
     * @param string $scope
     * @return array
     */
    private function getGenericModal(Phrase $title, $scope)
    {
        $provider = 'category_form.category_form_data_source';
        $listingTarget = $scope . '_listing';
        $selectionsColumns = 'cms_block_columns';

        // phpcs:disable Generic.Files.LineLength.TooLong
        $modal = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'options' => [
                            'title' => $title,
                            'buttons' => [
                                [
                                    'text' => __('Cancel'),
                                    'actions' => [
                                        'closeModal'
                                    ]
                                ], [
                                    'text' => __('Add Selected Banners'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . $listingTarget,
                                            'actionName' => 'save'
                                        ],
                                        'closeModal'
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                $listingTarget => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'insertListing',
                                'dataScope' => $listingTarget,
                                'externalProvider' => $listingTarget . '.' . $listingTarget . '_data_source',
                                'selectionsProvider' => $listingTarget . '.' . $listingTarget . '.' . $selectionsColumns . '.ids',
                                'ns' => $listingTarget,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'dataLinks' => [
                                    'imports' => false,
                                    'exports' => true
                                ],
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'links' => [
                                    'value' => "{$provider}:" . $listingTarget
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
        // phpcs:enable

        return $modal;
    }

    /**
     * @param string $scope
     * @return array
     */
    private function getGrid($scope)
    {
        $dataProvider = $scope . '_listing';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'addButton' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => 'data.promo_banners',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $dataProvider,
                        'dndConfig' => [
                            'enabled' => false
                        ],
                        'map' => [
                            'id' => 'block_id',
                            'title' => 'title',
                            'identifier' => 'identifier',
                            'position' => 'position',
                        ],
                        'links' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ],
                        'parentScope' => 'data',
                        'sortOrder' => 2,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => $this->fillMeta(),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function fillMeta()
    {
        return [
            'id' => $this->getTextColumn('id', false, __('ID'), 0),
            'title' => $this->getTextColumn('title', false, __('Title'), 10),
            'identifier' => $this->getTextColumn('identifier', false, __('Identifier'), 20),
            'style' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Text::NAME,
                            'formElement' => Input::NAME,
                            'componentType' => Field::NAME,
                            'dataScope' => 'style',
                            'label' => __('Style'),
                            'sortOrder' => 30,
                            'visible' => true
                        ],
                    ],
                ],
            ],
            'position' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Number::NAME,
                            'formElement' => Input::NAME,
                            'componentType' => Field::NAME,
                            'dataScope' => 'position',
                            'label' => __('Position'),
                            'sortOrder' => 40,
                            'visible' => true
                        ],
                    ],
                ],
            ],
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 70,
                            'fit' => true,
                        ],
                    ],
                ],
            ]
        ];
    }

    /**
     * @param string $dataScope
     * @param bool $fit
     * @param Phrase $label
     * @param int $sortOrder
     * @return array
     */
    private function getTextColumn(string $dataScope, bool $fit, Phrase $label, int $sortOrder)
    {
        $column = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                        'component' => 'Magento_Ui/js/form/element/text',
                        'dataType' => Text::NAME,
                        'dataScope' => $dataScope,
                        'fit' => $fit,
                        'label' => $label,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $column;
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

        if (!isset($data[$categoryId]['promo_banners'])) {
            return $data;
        }

        $promoBanners = $this->serializer->unserialize($category->getPromoBanners());
        $data[$categoryId]['promo_banners'] = [];

        foreach ($this->getDataScopes() as $dataScope) {
            $data[$categoryId]['promo_banners']['data']['promo_banners'][$dataScope] = [];
            foreach ($promoBanners as $banner) {
                $data[$categoryId]['promo_banners']['data']['promo_banners'][$dataScope][] = $this->fillData($banner);
            }
        }

        return $data;
    }

    /**
     * @param array $bannerData
     * @return array
     */
    private function fillData(array $bannerData)
    {
        return [
            'id' => $bannerData[BlockInterface::BLOCK_ID],
            'title' => $bannerData[BlockInterface::TITLE] ?? '',
            'identifier' => $bannerData[BlockInterface::IDENTIFIER] ?? '',
            'style' => $bannerData['style'] ?? '',
            'position' => $bannerData['position']
        ];
    }

    /**
     * @return array
     */
    private function getDataScopes()
    {
        return [
            self::DATA_SCOPE_CMS_BLOCK,
        ];
    }
}
