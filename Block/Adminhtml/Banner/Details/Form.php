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

    function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection $collection,
        array $data = []
    )
    {
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
                'rows' => 30,
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
        $data['html_code'] = $this->getBannerHtml($data);
        $data['dimensions'] = $data['width'] . 'x' . $data['height'];
        return $data;
    }

    private function getBannerHtml($bannerData)
    {
        $htmlId = $this->getValidHtmlId($bannerData['id']);
        return '<!-- Topsort Banner Code Start -->
<div style="width: ' . intval($bannerData['width']) . 'px; height: ' . intval($bannerData['height']) . 'px" id="topsort-banner-' . $htmlId . '"></div>
<script type="text/javascript">
window.topsortBanners = window.topsortBanners || {};
window.topsortBanners["' . $bannerData['id'] . '"] = {
    "bannerId": "' . $bannerData['id'] . '",
    "elId": "topsort-banner-' . $htmlId . '",
    "placement": "' . $bannerData['placement'] . '"
};
</script>
<script type="text/x-magento-init">
{
    "#topsort-banner-' . $htmlId . '": {
        "topsort-banner": {
            "bannerId": "' . $bannerData['id'] . '",
            "height": ' . intval($bannerData['height']) . ',
            "width": ' . intval($bannerData['width']) . ',
            "placement": "' . $bannerData['placement'] . '"
        }
    }
}
</script>
<!-- Topsort Banner Code End -->
';
    }

    private function getValidHtmlId($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }
}
