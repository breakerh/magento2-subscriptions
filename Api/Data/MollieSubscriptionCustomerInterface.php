<?php
/**
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mollie\Subscriptions\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface MollieSubscriptionCustomerInterface extends ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const MOLLIE_SUBSCRIPTION_CUSTOMER_ID = 'mollie_subscription_customer_id';

    /**
     * Get customer_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set customer_id
     * @param string $entityId
     * @return \Mollie\Subscriptions\Api\Data\MollieSubscriptionCustomerInterface
     */
    public function setEntityId($entityId);

    /**
     * Get customer_id
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param int $customerId
     * @return \Mollie\Subscriptions\Api\Data\MollieSubscriptionCustomerInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get mollie_customer_id
     * @return string|null
     */
    public function getMollieSubscriptionCustomerId();

    /**
     * Set mollie_customer_id
     * @param int $mollieMollieSubscriptionCustomerId
     * @return \Mollie\Subscriptions\Api\Data\MollieSubscriptionCustomerInterface
     */
    public function setMollieSubscriptionCustomerId($mollieMollieSubscriptionCustomerId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Mollie\Subscriptions\Api\Data\MollieSubscriptionCustomerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Mollie\Subscriptions\Api\Data\MollieSubscriptionCustomerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Mollie\Subscriptions\Api\Data\MollieSubscriptionCustomerExtensionInterface $extensionAttributes
    );
}
