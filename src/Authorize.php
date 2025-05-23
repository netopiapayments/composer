<?php 
namespace Netopia\Payment2;

class Authorize extends Start{
    public $backUrl;
    public $paReq;
    

    public function validateParam() {
        if(!isset($this->apiKey) || empty($this->apiKey)){
            throw new \Exception('apiKey is not defined');
        }

        if(!isset($this->paReq) || empty($this->paReq)){
            throw new \Exception('paReq Url is not defined');
        }

        if(!isset($this->backUrl) || empty($this->backUrl)){
            throw new \Exception('back Url is not defined');
        }

        if(!isset($this->bankUrl) || empty($this->bankUrl)){
            throw new \Exception('Bank Url is not defined for authorizing');
        }
    }
}