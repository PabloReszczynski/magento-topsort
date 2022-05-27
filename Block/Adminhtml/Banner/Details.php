<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Block\Adminhtml\Banner;

class Details extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Update Save and Delete buttons. Remove Delete button if group can't be deleted.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_headerText = __('Banner Details');

        $this->removeButton('save');
        $this->removeButton('delete');
        $this->removeButton('reset');
    }

    function _prepareLayout()
    {
        $this->addChild('form', \Topsort\Integration\Block\Adminhtml\Banner\Details\Form::class);
        return parent::_prepareLayout();
    }

    /**
     * Return form block HTML
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __getForm()
    {
        return $this->getLayout()
            ->createBlock(\Topsort\Integration\Block\Adminhtml\Banner\Details\Form::class)
            ->toHtml();
    }
}
