<?php 
namespace Netopia\Payment2;

class Request extends Start
{
    public $authenticationToken;
    public $ntpID;
    public $jsonRequest;


    public function setConfig(array $configData): array
    {
        return array(
            'emailTemplate' => (string) isset($configData['emailTemplate']) ? $configData['emailTemplate'] : 'confirm',
            'notifyUrl'     => (string) $configData['notifyUrl'],
            'redirectUrl'   => (string) $configData['redirectUrl'],
            'language'      => (string) isset($configData['language']) ? $configData['language'] : 'RO'
        );
    }

    public function setPayment(array $cardData, string|null $threeDSecusreData): array
    {
        if (!empty($threeDSecusreData)) {
            $threeDSecusreData = json_decode($threeDSecusreData);
            $threeDSecusreData->IP_ADDRESS = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1";
        }

        return array(
            'options' => [
                'installments' => (int) 1,
                'bonus'        => (int) 0
            ],
            'instrument' => [
                'type'          => 'card',
                'account'       => isset($cardData['account']) ? (string) $cardData['account'] : '',
                'expMonth'      => isset($cardData['expMonth']) ? (int) $cardData['expMonth'] : 0,
                'expYear'       => isset($cardData['expYear']) ? (int) $cardData['expYear'] : 0,
                'secretCode'    => isset($cardData['secretCode']) ? (string) $cardData['secretCode'] : '',
                'token'         => isset($cardData['token']) ? (string) $cardData['token'] : null,
            ],
            'data' =>  $threeDSecusreData
        );
    }

    /**
     * Set the order
     */
    public function setOrder(\stdClass $orderData): array
    {
        return array(
            'ntpID'         => (string) null, 
            'posSignature'  => (string) $this->posSignature,
            'dateTime'      => (string) date("c", strtotime(date("Y-m-d H:i:s"))),
            'description'   => (string) $orderData->description,
            'orderID'       => (string) $orderData->orderID,
            'amount'        => (float)  $orderData->amount,
            'currency'      => (string) $orderData->currency,
            'billing'       => [
                'email'         => (string) $orderData->billing->email,
                'phone'         => (string) $orderData->billing->phone,
                'firstName'     => (string) $orderData->billing->firstName,
                'lastName'      => (string) $orderData->billing->lastName,
                'city'          => (string) $orderData->billing->city,
                'country'       => (int)    $orderData->billing->country,
                'state'         => (string) $orderData->billing->state,
                'postalCode'    => (string) $orderData->billing->postalCode,
                'details'       => (string) $orderData->billing->details
            ],
            'shipping'      => [
                'email'         => (string) $orderData->shipping->email,
                'phone'         => (string) $orderData->shipping->phone,
                'firstName'     => (string) $orderData->shipping->firstName,
                'lastName'      => (String) $orderData->shipping->lastName,
                'city'          => (string) $orderData->shipping->city,
                'country'       => (int)    $orderData->shipping->country,
                'state'         => (string) $orderData->shipping->state,
                'postalCode'    => (string) $orderData->shipping->postalCode,
                'details'       => (string) $orderData->shipping->details
            ],
            'products' => $orderData->products,
            'installments' => array(
                'selected' => (int)1,
                'available' => [(int)0]
            ),
            'data' => isset($orderData->customData) ? (array) $orderData->customData : null,
            'scaExemptionInd' => isset($orderData->scaExemptionInd) ? (string) $orderData->scaExemptionInd : null,
        );
    }


    /**
     * Set the request to payment
     * @output json
     */
    public function setRequest(
        array $configData,
        array $cardData,
        \stdClass $orderData,
        string|null $threeDSecusreData = null
    ): void {
        $startArr = array(
          'config'  => $this->setConfig($configData),
          'payment' => $this->setPayment($cardData, $threeDSecusreData),
          'order'   => $this->setOrder($orderData)
      );
      
      // make json Data 
      $this->jsonRequest = json_encode($startArr);
    }

    public function startPayment(): string
    {
      return $this->sendRequest($this->jsonRequest);
    }    
}