<?php

namespace PrestaShop\PrestaShop\Core\Payment;

class PaymentOption
{
    public $moduleName;
    public $callToActionText;
    public $action;
    public $additionalInformation;

    public function setModuleName($name)
    {
        $this->moduleName = $name;

        return $this;
    }

    public function setCallToActionText($text)
    {
        $this->callToActionText = $text;

        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    public function setAdditionalInformation($info)
    {
        $this->additionalInformation = $info;

        return $this;
    }
}
