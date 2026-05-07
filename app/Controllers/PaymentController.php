<?php

class PaymentController extends Controller {

    public function stripeSuccess(): void {
        $sessionId = $this->request->get('session_id');
        $orderNum  = $this->request->get('order');
        try {
            (new StripeService())->verifyCheckoutSession($sessionId, $orderNum);
        } catch (Throwable $e) { logError('Stripe success error: ' . $e->getMessage()); }
        redirect("/shop/{$this->request->get('store')}/order/{$orderNum}/success");
    }

    public function stripeCancel(): void {
        redirect("/shop/{$this->request->get('store')}/order/{$this->request->get('order')}/failed");
    }

    public function razorpaySuccess(): void {
        $payload = $this->request->all();
        try {
            (new RazorpayService())->verifyPayment($payload);
            redirect("/shop/{$payload['store']}/order/{$payload['order']}/success");
        } catch (Throwable $e) {
            redirect("/shop/{$payload['store']}/order/{$payload['order']}/failed");
        }
    }

    public function paypalSuccess(): void {
        $orderId = $this->request->get('token');
        $orderNum = $this->request->get('order');
        try {
            (new PayPalService())->captureOrder($orderId, $orderNum);
            redirect("/shop/{$this->request->get('store')}/order/{$orderNum}/success");
        } catch (Throwable $e) {
            redirect("/shop/{$this->request->get('store')}/order/{$orderNum}/failed");
        }
    }

    public function paypalCancel(): void {
        redirect("/shop/{$this->request->get('store')}/order/{$this->request->get('order')}/failed");
    }
}
