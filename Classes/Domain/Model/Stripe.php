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
    protected $paymentSessionId = 0;

    /**
     * @return string
     */
    public function getPaymentSessionId(): string
    {
        return $this->paymentSessionId;
    }

    /**
     * @param string $paymentSessionId
     * @return void
     */
    public function setPaymentSessionId(string $paymentSessionId): void
    {
        $this->paymentSessionId = $paymentSessionId;
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
