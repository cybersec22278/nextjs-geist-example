<?php
require_once 'config.php';

class NowPaymentsAPI {
    private $apiKey;
    private $apiUrl;
    private $ipnSecret;

    public function __construct($apiKey, $apiUrl, $ipnSecret) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->ipnSecret = $ipnSecret;
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $ch = curl_init();
        $url = $this->apiUrl . $endpoint;
        
        $headers = [
            'x-api-key: ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception('API request failed with status ' . $httpCode);
        }

        return json_decode($response, true);
    }

    public function createPayment($amount, $currency = 'USD') {
        $data = [
            'price_amount' => $amount,
            'price_currency' => $currency,
            'order_id' => uniqid('order_'),
            'order_description' => 'Deposit to Stresser',
            'ipn_callback_url' => 'https://your-domain.com/ipn.php', // Replace with your domain
            'success_url' => 'https://your-domain.com/payment_success.php', // Replace with your domain
            'cancel_url' => 'https://your-domain.com/payment_cancel.php' // Replace with your domain
        ];

        return $this->makeRequest('/payment', 'POST', $data);
    }

    public function getPaymentStatus($paymentId) {
        return $this->makeRequest("/payment/$paymentId");
    }

    public function verifyIpnSignature($payload, $signature) {
        $sortedPayload = $this->sortIpnKeys($payload);
        $encodedPayload = json_encode($sortedPayload, JSON_UNESCAPED_SLASHES);
        $calculatedSignature = hash_hmac('sha512', $encodedPayload, $this->ipnSecret);
        
        return hash_equals($calculatedSignature, $signature);
    }

    private function sortIpnKeys($payload) {
        if (is_array($payload)) {
            ksort($payload);
            foreach ($payload as $key => $value) {
                $payload[$key] = $this->sortIpnKeys($value);
            }
        }
        return $payload;
    }
}

// Initialize NowPayments API
$nowpayments = new NowPaymentsAPI(
    NOWPAYMENTS_API_KEY,
    NOWPAYMENTS_API_URL,
    NOWPAYMENTS_IPN_SECRET
);

// Helper Functions
function createPayment($amount) {
    global $nowpayments;
    try {
        return $nowpayments->createPayment($amount);
    } catch (Exception $e) {
        error_log('Payment creation failed: ' . $e->getMessage());
        return null;
    }
}

function getPaymentHistory($username) {
    $payments = [];
    $file = 'database/payments.txt';
    
    if (!file_exists($file)) {
        return $payments;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $data = explode('|', $line);
        if ($data[0] === $username) {
            $payments[] = [
                'date' => $data[1],
                'amount' => $data[2],
                'status' => $data[3],
                'payment_id' => $data[4]
            ];
        }
    }

    return array_reverse($payments);
}

function recordPayment($username, $amount, $status, $paymentId) {
    $file = 'database/payments.txt';
    $date = date('Y-m-d H:i:s');
    $data = "$username|$date|$amount|$status|$paymentId\n";
    
    file_put_contents($file, $data, FILE_APPEND);
}

function updatePaymentStatus($paymentId, $newStatus) {
    $file = 'database/payments.txt';
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newContent = '';
    
    foreach ($lines as $line) {
        $data = explode('|', $line);
        if ($data[4] === $paymentId) {
            $data[3] = $newStatus;
            $newContent .= implode('|', $data) . "\n";
        } else {
            $newContent .= $line . "\n";
        }
    }
    
    file_put_contents($file, $newContent);
}
