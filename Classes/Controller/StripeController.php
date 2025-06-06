<?php
namespace NeosRulez\Shop\Payment\Stripe\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use NeosRulez\Shop\Domain\Repository\OrderRepository;
use NeosRulez\Shop\Payment\Stripe\Domain\Repository\StripeRepository;
use NeosRulez\Shop\Payment\Stripe\Payment\Stripe;
use NeosRulez\Shop\Service\FinisherService;

/**
 * @Flow\Scope("singleton")
 */
class StripeController extends ActionController
{

    /**
     * @Flow\Inject
     * @var Stripe
     */
    protected $stripe;

    /**
     * @Flow\Inject
     * @var StripeRepository
     */
    protected $stripeRepository;

    /**
     * @Flow\Inject
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @Flow\Inject
     * @var FinisherService
     */
    protected $finisherService;

    /**
     * @param int $orderNumber
     * @return bool
     */
    public function paymentAction(int $orderNumber): bool
    {
        return $this->stripe->isPaid($orderNumber);
    }

    /**
     * @return bool
     */
    public function webhookAction(): bool
    {
        if(array_key_exists('HTTP_STRIPE_SIGNATURE', $_SERVER)) {
            $sessionId = $this->stripe->webhook();
            if($sessionId !== '') {
                $stripeSessions = $this->stripeRepository->findByCheckoutSessionId($sessionId);
                if($stripeSessions->count() > 0)
                    $stripeSession = $stripeSessions->getFirst();

                $order = $this->orderRepository->findByOrdernumber($stripeSession->getOrderNumber());
                $order->setPaid(true);
                $this->finisherService->initAfterPaymentFinishers($order->getInvoicedata());
                $this->orderRepository->update($order);
                $this->persistenceManager->persistAll();
                $this->response->setStatusCode(200);
                return true;
            }
        }
        $this->response->setStatusCode(404);
        return false;
    }

    /**
     * @param int $orderNumber
     * @return bool
     */
    public function webhookTestAction(int $orderNumber): bool
    {
        $order = $this->orderRepository->findByOrdernumber($orderNumber);
        $order->setPaid(true);
        $this->finisherService->initAfterPaymentFinishers($order->getInvoicedata());
        $this->orderRepository->update($order);
        $this->persistenceManager->persistAll();
        $this->response->setStatusCode(200);
        return true;
    }

}
