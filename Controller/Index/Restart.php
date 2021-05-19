<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mollie\Subscriptions\Controller\Index;

use Magento\Catalog\Model\Product;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mollie\Api\Resources\Subscription;
use Mollie\Payment\Config;
use Mollie\Payment\Model\Mollie;
use Mollie\Subscriptions\Api\Data\SubscriptionToProductInterface;
use Mollie\Subscriptions\Api\Data\SubscriptionToProductInterfaceFactory;
use Mollie\Subscriptions\Api\SubscriptionToProductRepositoryInterface;

class Restart extends Action implements HttpPostActionInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Mollie
     */
    private $mollie;

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var SubscriptionToProductInterfaceFactory
     */
    private $subscriptionToProductFactory;

    /**
     * @var SubscriptionToProductRepositoryInterface
     */
    private $subscriptionToProductRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Product
     */
    private $product;

    public function __construct(
        Context $context,
        Config $config,
        Mollie $mollie,
        CurrentCustomer $currentCustomer,
        Session $customerSession,
        SubscriptionToProductInterfaceFactory $subscriptionToProductFactory,
        SubscriptionToProductRepositoryInterface $subscriptionToProductRepository,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        Product $product
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->mollie = $mollie;
        $this->currentCustomer = $currentCustomer;
        $this->customerSession = $customerSession;
        $this->subscriptionToProductFactory = $subscriptionToProductFactory;
        $this->subscriptionToProductRepository = $subscriptionToProductRepository;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->product = $product;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        $customer = $this->currentCustomer->getCustomer();
        $extensionAttributes = $customer->getExtensionAttributes();

        $api = $this->mollie->getMollieApi();
        $subscriptionId = $this->getRequest()->getParam('subscription_id');

        $canceledSubscription = $api->subscriptions->getForId(
            $extensionAttributes->getMollieCustomerId(),
            $subscriptionId
        );

        try {
            $subscription = $api->subscriptions->createForId($extensionAttributes->getMollieCustomerId(), [
                'amount' => [
                    'currency' => $canceledSubscription->amount->currency,
                    'value' => $canceledSubscription->amount->value,
                ],
                'times' => $canceledSubscription->times,
                'interval' => $canceledSubscription->interval,
                'description' => $canceledSubscription->description,
                'metadata' => $this->getMetadata($canceledSubscription),
                'webhookUrl' => $this->_url->getUrl('mollie-subscriptions/api/webhook'),
            ]);

            $this->saveSubscriptionResult($subscription);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('We are unable to restart the subscription'));

            $this->config->addToLog('error', [
                'message' => 'Unable to restart the subscription with ID ' . $canceledSubscription->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return $this->_redirect('*/*/index');
        }


        $this->messageManager->addSuccessMessage('The subscription has been restarted successfully');

        return $this->_redirect('*/*/index');
    }

    private function getMetadata(Subscription $canceledSubscription)
    {
        // Ignore as it has the wrong doctype:
        // https://github.com/mollie/mollie-api-php/pull/554
        // @phpstan-ignore-next-line
        if ($canceledSubscription->metadata instanceof \stdClass) {
            $metadata = $canceledSubscription->metadata;
            $metadata->parent_id = $canceledSubscription->id;

            return $metadata;
        }

        return [];
    }

    private function saveSubscriptionResult(Subscription $subscription)
    {
        // Ignore as it has the wrong doctype:
        // https://github.com/mollie/mollie-api-php/pull/554
        // @phpstan-ignore-next-line
        $productId = $this->product->getIdBySku($subscription->metadata->sku);

        /** @var SubscriptionToProductInterface $model */
        $model = $this->subscriptionToProductFactory->create();
        $model->setCustomerId($subscription->customerId);
        $model->setSubscriptionId($subscription->id);
        $model->setProductId($productId);
        $model->setStoreId($this->storeManager->getStore()->getId());

        $model = $this->subscriptionToProductRepository->save($model);

        $this->eventManager->dispatch('mollie_subscription_created', ['subscription' => $model]);
        $this->eventManager->dispatch('mollie_subscription_restarted', ['subscription' => $model]);
    }
}
