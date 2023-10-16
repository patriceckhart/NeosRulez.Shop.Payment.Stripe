<?php
namespace NeosRulez\Shop\Payment\Stripe\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Stripe
{

    /**
     * @var int
     */
    protected $orderNumber = 0;

    /**
     * @return int
     */
    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     * @return void
     */
    public function setOrderNumber(int $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @var string
     */
    protected $checkoutSessionId = 0;

    /**
     * @return string
     */
    public function getCheckoutSessionId(): string
    {
        return $this->checkoutSessionId;
    }

    /**
     * @param string $checkoutSessionId
     * @return void
     */
    public function setCheckoutSessionId(string $checkoutSessionId): void
    {
        $this->checkoutSessionId = $checkoutSessionId;
    }

    /**
     * @var \DateTime
     */
    protected $created;


    public function __construct() {
        $this->created = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

}
