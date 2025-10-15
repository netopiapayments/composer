<?php
namespace Netopia\Payment2;

class Request extends Start {
    public $posSignature;
    public $apiKey;
    public $isLive;

    public $notifyUrl;
    public $redirectUrl;
    public $jsonRequest;

    // -------------------
    public function setConfig($configData) {
        if (is_string($configData)) {
            $configData = json_decode($configData, true);
            if ($configData === null) {
                throw new \InvalidArgumentException("Invalid JSON passed to setConfig()");
            }
        }

        $config = [
            'emailTemplate' => isset($configData['emailTemplate']) ? (string)$configData['emailTemplate'] : 'confirm',
            'notifyUrl'     => isset($configData['notifyUrl']) ? (string)$configData['notifyUrl'] : null,
            'redirectUrl'   => isset($configData['redirectUrl']) ? (string)$configData['redirectUrl'] : null,
            'language'      => isset($configData['language']) ? (string)$configData['language'] : 'RO'
        ];

        $this->notifyUrl   = $config['notifyUrl'];
        $this->redirectUrl = $config['redirectUrl'];
        return $config;
    }

    // -------------------
    public function setPayment($cardData, $threeDSecureData = []) {
        if (is_string($threeDSecureData)) {
            $threeDSecureData = json_decode($threeDSecureData, true);
        }
        $threeDSecureData = $threeDSecureData ?? [];
        $threeDSecureData['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR'] ?? "127.0.0.1";

        $cardData = $cardData ?? [];

        $payment = [
            'options' => [
                'installments' => 1,
                'bonus' => 0
            ],
            'instrument' => [
                'type' => "card",
                'account' => $cardData['account'] ?? '',
                'expMonth' => (int)($cardData['expMonth'] ?? 0),
                'expYear' => (int)($cardData['expYear'] ?? 0),
                'secretCode' => $cardData['secretCode'] ?? '',
                'token' => null
            ],
            'data' => $threeDSecureData
        ];
        return $payment;
    }

    // -------------------
    public function setOrder($orderData) {
        // Ensure $orderData is array
        $orderData = (array)$orderData;
        $billing  = (array)($orderData['billing'] ?? []);
        $shipping = (array)($orderData['shipping'] ?? []);
        $products = $orderData['products'] ?? [];

        $order = [
            'ntpID'        => null,
            'posSignature' => $this->posSignature ?? '',
            'dateTime'     => date("c"),
            'description'  => $orderData['description'] ?? '',
            'orderID'      => $orderData['orderID'] ?? '',
            'amount'       => (float)($orderData['amount'] ?? 0),
            'currency'     => $orderData['currency'] ?? 'RON',
            'billing'      => $billing,
            'shipping'     => $shipping,
            'products'     => $products,
            'installments' => [
                'selected'  => 1,
                'available' => [0]
            ],
            'data' => null
        ];

        return $order;
    }

    // -------------------
    public function setRequest($configData, $cardData, $orderData, $threeDSecureData = []) {
        $startArr = [
            'config'  => $this->setConfig($configData),
            'payment' => $this->setPayment($cardData, $threeDSecureData),
            'order'   => $this->setOrder($orderData)
        ];

        $this->jsonRequest = json_encode($startArr);
        return $this->jsonRequest;
    }

    // -------------------
    public function startPayment() {
        if (empty($this->jsonRequest)) {
            throw new \Exception("Request not initialized. Call setRequest() first.");
        }

        $result = $this->sendRequest($this->jsonRequest);
        return $result;
    }
}
