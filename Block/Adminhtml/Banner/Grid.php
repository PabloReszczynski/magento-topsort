<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Block\Adminhtml\Banner;

use Magento\Framework\Data\Collection;

/**
 * Adminhtml Bim Datasets grid
 *
 * @category Bim
 * @package Bim_Pbi
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Topsort\Integration\Model\ResourceModel\Banner\Grid\CollectionFactory
     */
    private $collectionFactory;

    function __construct(
        \Topsort\Integration\Model\ResourceModel\Banner\Grid\CollectionFactory $collectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = [])
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Internal constructor, that is called from real constructor
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_filterVisibility = false;
        $this->_pagerVisibility = false;
    }

    /**
     * Prepare grid collection object
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        try {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            foreach ($collection as $item) {
                $item->setDimensions($item->getWidth() . 'x' . $item->getHeight());
            }

            $this->setCollection($collection);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__('Collection initialization failed. See logs for details.'));
        }

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'placement',
            array(
                'header' => __('Placement'),
                'index' => 'placement',
            )
        );

        $this->addColumn(
            'dimensions',
            array(
                'header' => __('Dimensions'),
                'index' => 'dimensions',
            )
        );

        return $this;
    }

    /**
     * Row action URL
     *
     * @param \Magento\Framework\DataObject $row
     * @return bool
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/details', ['id'=> $row->getData('id')]);
    }
}