<?php
/**
* NOTICE OF LICENSE
*
* The MIT License (MIT)
*
* Copyright (c) 2019 CoinCorner
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of
* this software and associated documentation files (the "Software"), to deal in
* the Software without restriction, including without limitation the rights to use,
* copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
* and to permit persons to whom the Software is furnished to do so, subject
* to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
* IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
*  @author    CoinCorner <support@CoinCorner.com>
*  @copyright 2015-2016 CoinCorner
*  @license   The MIT License (MIT)
*/

class CoincornerRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;

        $total = (float)number_format($cart->getOrderTotal(true, 3), 2, '.', '');
        $currency = Context::getContext()->currency;

        $description = array();

        foreach ($cart->getProducts() as $product) {
            $description[] = $product['cart_quantity'] . ' × ' . $product['name'];
        }

        $customer = new Customer($cart->id_customer);

        $link = new Link();
        $success_url = $link->getPageLink('order-confirmation', null, null, array(
          'id_cart'     => $cart->id,
          'id_module'   => $this->module->id,
          'key'         => $customer->secure_key
        ));

        $APIKey = Configuration::get('COINCORNER_API_KEY');
        $APISecret = Configuration::get('COINCORNER_API_SECRET');
        $UserId = Configuration::get('COINCORNER_USER_ID');
        $SettleCurrency = Configuration::get('COINCORNER_SETTLE_CURRENCY_DEFAULT');
        $InvoiceCurrency = Configuration::get('COINCORNER_INVOICE_CURRENCY_DEFAULT');

        $url = 'https://checkout.coincorner.com/api/CreateOrder';
        $nonce = (int)(microtime(true) * 1e6);
        $sig = hash_hmac('sha256', $nonce . $UserId . $APIKey, $APISecret);
        $headers   = array();
        $order = array(
            'OrderId'    => $cart->id,
            'InvoiceAmount'     => $total,
            'SettleCurrency'    => $SettleCurrency,
            'InvoiceCurrency'   => $InvoiceCurrency,
            'FailRedirectURL'  => $this->context->link->getModuleLink('coincorner', 'cancel'),
            'NotificationURL'  => $this->context->link->getModuleLink('coincorner', 'callback'),
            'SuccessRedirectURL'      => $success_url,
            'ItemDescription'      => join($description, ', '),
            'APIKey' => $APIKey,
            'Nonce' => $nonce,
            'Signature' => $sig
        );

        $curl = curl_init();

        $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL  => $url
        );
         
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        array_merge($curl_options, array(CURLOPT_POST => 1));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($order));
      
        curl_setopt_array($curl, $curl_options);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
   
        $response = json_decode(curl_exec($curl), true);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($http_status != 200) {
            Tools::redirect('index.php?controller=order&step=3');
        }
        else {
            $this->module->validateOrder(
                $cart->id,
                Configuration::get('COINCORNER_PENDING'),
                $total,
                'coincorner',
                null,
                null,
                (int)$currency->id,
                false,
                $customer->secure_key
            );
            
            Tools::redirect($response);
        }
 
        
    }
}
