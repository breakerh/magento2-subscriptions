<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Mollie\Subscriptions\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mollie\Subscriptions\Config\Source\IntervalType;
use Mollie\Subscriptions\Config\Source\RepetitionType;
use Mollie\Subscriptions\Config\Source\Status;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        if (version_compare($context->getVersion(), '0.1.0', '<=')) {
            $this->addProductAttribute($setup, $eavSetup);
        }

        $setup->endSetup();
    }

    private function addProductAttribute(ModuleDataSetupInterface $setup, EavSetup $eavSetup)
    {
        $eavSetup = $this->eavSetupFactory->create();

        // interval amount = 3
        // interval type = day, month, week
        // interval repetition = infinite/limited

        if (!$eavSetup->getAttribute(Product::ENTITY, 'mollie_subscription_product')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'mollie_subscription_product',
                [
                    'group' => 'Mollie',
                    'label' => 'Is this a subscription product?',
                    'type' => 'int',
                    'input' => 'boolean',
                    'required' => false,
                    'sort_order' => 10,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'visible' => true,
                    'visible_on_front' => true,
                    'frontend' => '',
                    'class' => '',
                    'source' => Status::class,
                    'user_defined' => false,
                    'default' => '0',
                ]
            );
        }

        if (!$eavSetup->getAttribute(Product::ENTITY, 'mollie_subscription_interval_amount')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'mollie_subscription_interval_amount',
                [
                    'group' => 'Mollie',
                    'type' => 'int',
                    'label' => 'Repeat Payment Every',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 20,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'visible' => true,
                    'is_html_allowed_on_front' => false,
                    'visible_on_front' => true,
                ]
            );
        }

        if (!$eavSetup->getAttribute(Product::ENTITY, 'mollie_subscription_interval_type')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'mollie_subscription_interval_type',
                [
                    'group' => 'Mollie',
                    'type' => 'text',
                    'label' => 'Subscription Interval Type',
                    'input' => 'select',
                    'source' => IntervalType::class,
                    'required' => false,
                    'sort_order' => 30,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'visible' => true,
                    'is_html_allowed_on_front' => false,
                    'visible_on_front' => true,
                ]
            );
        }

        if (!$eavSetup->getAttribute(Product::ENTITY, 'mollie_subscription_repetition_amount')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'mollie_subscription_repetition_amount',
                [
                    'group' => 'Mollie',
                    'type' => 'int',
                    'label' => 'Repeat Payment',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 40,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'visible' => true,
                    'is_html_allowed_on_front' => false,
                    'visible_on_front' => true,
                ]
            );
        }

        if (!$eavSetup->getAttribute(Product::ENTITY, 'mollie_subscription_repetition_type')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'mollie_subscription_repetition_type',
                [
                    'group' => 'Mollie',
                    'type' => 'text',
                    'label' => 'Subscription Repetition Type',
                    'input' => 'select',
                    'source' => RepetitionType::class,
                    'required' => false,
                    'sort_order' => 50,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'visible' => true,
                    'is_html_allowed_on_front' => false,
                    'visible_on_front' => true,
                ]
            );
        }
    }
}
