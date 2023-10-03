<?php
namespace NeosRulez\Shop\Payment\Stripe\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;
use NeosRulez\Shop\Domain\Model\Order;
use NeosRulez\Shop\Payment\Stripe\Domain\Model\Stripe;

/**
 * @Flow\Scope("singleton")
 */
class StripeRepository extends Repository
{

    /**
     * @param int $orderNumber
     * @param string $paymentSessionId
     * @return void
     */
    public function createStripePayment(int $orderNumber, string $paymentSessionId): void
    {
        $newStripe = new Stripe();
        $newStripe->setOrderNumber($orderNumber);
        $newStripe->setPaymentSessionId($paymentSessionId);
        $this->add($newStripe);
    }

}
