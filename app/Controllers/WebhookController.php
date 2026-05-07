<?php

class WebhookController extends Controller {

    public function stripe(): void {
        $payload   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        try {
            $service = new StripeService();
            $service->handleWebhook($payload, $signature);
            $this->json(['received' => true]);
        } catch (Throwable $e) {
            logError('Stripe webhook error: ' . $e->getMessage());
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function razorpay(): void {
        $payload   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
        try {
            $service = new RazorpayService();
            $service->handleWebhook($payload, $signature);
            $this->json(['received' => true]);
        } catch (Throwable $e) {
            logError('Razorpay webhook error: ' . $e->getMessage());
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function paypal(): void {
        $payload = file_get_contents('php://input');
        try {
            $service = new PayPalService();
            $service->handleWebhook($payload, $_SERVER);
            $this->json(['received' => true]);
        } catch (Throwable $e) {
            logError('PayPal webhook error: ' . $e->getMessage());
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
