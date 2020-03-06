<?php

namespace TF\StoreHours\Setup\Patch\Data;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class CreateClosedPage
 * @package TF\StoreHours\Setup\Patch
 */
class CreateClosedPage implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;


    /**
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $pageData = [
            'title' => 'Store Closed',
            'page_layout' => '1column',
            'meta_keywords' => '',
            'meta_description' => '',
            'identifier' => 'store-closed',
            'content_heading' => 'Store Closed',
            'content' => '<p>The store is currently closed.</p>',
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];

        return $this->pageFactory->create()->setData($pageData)->save();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
