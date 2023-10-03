<?php
namespace NeosRulez\Shop\Payment\Stripe\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use NeosRulez\Shop\Payment\Stripe\Payment\Stripe;

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
     * @param int $orderNumber
     * @return bool
     */
    public function paymentAction(int $orderNumber): bool
    {
        return $this->stripe->isPaid($orderNumber);
    }

}
