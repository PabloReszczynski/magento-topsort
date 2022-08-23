<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */

namespace Topsort\Integration\Block\Adminhtml\Banner\Details;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection
     */
    private $collection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection $collection,
        array $data = []
    ) {
        $this->collection = $collection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form for render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Banner Information')]);

        $fieldset->addField(
            'placement',
            'text',
            [
                'name' => 'placement',
                'label' => __('Placement'),
                'readonly'  => true,
                'required'  => false
            ]
        );

        $fieldset->addField(
            'dimensions',
            'text',
            [
                'name' => 'dimensions',
                'label' => __('Dimensions'),
                'readonly'  => true,
                'required'  => false
            ]
        );

        $afterElementHtml = '<small>Copy the HTML code and use it on a CSM page or block in order to place the banner on pages in Magento.</small>';

        $fieldset->addField(
            'html_code',
            'textarea',
            [
                'name' => 'html_code',
                'rows' => 3,
                'label' => __('HTML Code'),
                'title' => __('HTML Code'),
                'onclick' => "this.select()",
                'after_element_html' => $afterElementHtml,
                'readonly'  => true,
                'required'  => false
            ]
        );

        $form->addValues($this->getBannerData());

        $form->setUseContainer(true);
        $form->setAction($this->getUrl('#'));
        $form->setMethod('post');
        $this->setForm($form);
    }

    private function getBannerData()
    {
        $id = $this->getRequest()->getParam('id');
        $data = $this->collection->getBannerDataById($id);
        $data['html_code'] = $this->getBannerBlockHtml($data);
        $data['dimensions'] = $data['width'] . 'x' . $data['height'];
        return $data;
    }

    private function getBannerHtml($bannerData)
    {
        return $this->getLayout()->createBlock(\Topsort\Integration\Block\Banner::class, "banner-html", [
            'data' => [
                'banner_id' => $bannerData['id'],
                'width' => intval($bannerData['width']),
                'height' => intval($bannerData['height']),
                'placement' => $bannerData['placement']
            ]
        ])->toHtml();
    }

    private function getBannerBlockHtml($bannerData)
    {
        $class = \Topsort\Integration\Block\Banner::class;
        return "{{block class=\"$class\" banner_id=\"{$bannerData['id']}\" width=\"{$bannerData['width']}\" height=\"{$bannerData['height']}\" placement=\"{$bannerData['placement']}\"}}";
    }
}
