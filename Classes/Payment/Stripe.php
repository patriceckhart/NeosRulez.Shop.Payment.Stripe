<?php
namespace NeosRulez\Shop\Payment\Stripe\Payment;

use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use NeosRulez\Shop\Domain\Model\Order;
use NeosRulez\Shop\Payment\Payment\AbstractPayment;
use NeosRulez\Shop\Payment\Stripe\Domain\Repository\StripeRepository;
use Stripe\StripeClient;

/**
 * @Flow\Scope("singleton")
 */
class Stripe extends AbstractPayment
{

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var StripeRepository
     */
    protected $stripeRepository;

    /**
     * @param array $payment
     * @param array $args
     * @param string $successUri
     * @return string
     */
    public function execute(array $payment, array $args, string $successUri): string
    {
        $order = $this->orderRepository->findByOrderNumber($args['order_number']);
        $order->setCanceled(false);
        $order->setDone(true);
        $this->orderRepository->update($order);
        return $this->createPayment($payment['secretKey'], ((float) $args['summary']['total'] * 100), $args['order_number'], $successUri, $args['failure_uri'], $args['email']);
    }

    /**
     * @param string $secretKey
     * @param int $total
     * @param string $orderNumber
     * @param string $successUri
     * @param string $failureUri
     * @param string $email
     * @return string
     */
    private function createPayment(string $secretKey, int $total, string $orderNumber, string $successUri, string $failureUri, string $email): string
    {
        $stripe = new StripeClient(
            $secretKey
        );
        $product = $stripe->products->create([
            'name' => '#' . $orderNumber,
        ]);
        $price = $stripe->prices->create([
            'unit_amount' => $total,
            'currency' => 'eur',
            'product' => $product,
        ]);
        $session = $stripe->checkout->sessions->create([
            'success_url' => $this->generateSuccessUri($orderNumber, $successUri),
            'cancel_url' => $failureUri,
            'line_items' => [
                [
                    'price' => $price->id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'customer_email' => $email
        ]);

        $this->stripeRepository->createStripePayment($orderNumber, $session->id);

        return $session->url;
    }

    /**
     * @param int $orderNumber
     * @return bool
     */
    public function isPaid(int $orderNumber): bool
    {
        $order = $this->orderRepository->findByOrderNumber($orderNumber);
        $context = $this->contextFactory->create();
        $payment = $context->getNodeByIdentifier($order->getPayment());
        $stripeSession = $this->stripeRepository->findByOrderNumber($orderNumber)->count() > 0 ? $this->stripeRepository->findByOrderNumber($orderNumber)->getFirst() : false;
        if($stripeSession) {
            $paymentSessionId = $stripeSession->getPaymentSessionId();
            $stripe = new StripeClient(
                $payment->getProperty('secretKey')
            );
            $session = $stripe->checkout->sessions->retrieve(
                $paymentSessionId, []
            );
            if($session->status === 'complete') {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getPaymentNodeSecrets(): array
    {
        $context = $this->contextFactory->create();
        $paymentNode = (new FlowQuery(array($context->getCurrentSiteNode())))->find('[instanceof NeosRulez.Shop.Payment.Stripe:Payment.Stripe]')->context(array('workspaceName' => 'live'))->filter('[_hidden=false]')->sort('_index', 'ASC')->get();
        if(count($paymentNode) > 0 && $paymentNode[0]->hasProperty('webhookEndpointSecret') && $paymentNode[0]->hasProperty('secretKey')) {
            return [
                'webhookEndpointSecret' => $paymentNode[0]->getProperty('webhookEndpointSecret'),
                'secret' => $paymentNode[0]->getProperty('secretKey')
            ];
        }
        return [];
    }

    /**
     * @return string
     */
    public function webhook(): string
    {
        $paymentNode = $this->getPaymentNodeSecrets();
        if(!empty($paymentNode)) {
            $stripe = new StripeClient(
                $paymentNode['secret']
            );
            $endpoint_secret = $paymentNode['webhookEndpointSecret'];

            $payload = @file_get_contents('php://input');
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            $event = null;

            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
                );
            } catch(\UnexpectedValueException $e) {
                // Invalid payload
                http_response_code(400);
                exit();
            } catch(\Stripe\Exception\SignatureVerificationException $e) {
                // Invalid signature
                http_response_code(400);
                exit();
            }

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    if($session->payment_status === 'paid') {
                        return $session->id;
                    }
                    return '';
                default:
                    return '';
            }
        }
        return '';
    }

}
