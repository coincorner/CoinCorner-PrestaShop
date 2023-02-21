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
 * @author    CoinCorner <support@CoinCorner.com>
 * @copyright 2019 CoinCorner
 * @license    The MIT License (MIT)
 */

class CoincornerCallbackModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        $response = json_decode(Tools::file_get_contents('php://input'));
        $order_id = $response->OrderId;
        $API_Key_Request = $response->APIKey;
        $order = Order::getByCartId($order_id);
        $APIKey = Configuration::get('COINCORNER_API_KEY');
        $APISecret = Configuration::get('COINCORNER_API_SECRET');
        $UserId = Configuration::get('COINCORNER_USER_ID');

        $url = 'https://checkout.coincorner.com/api/CheckOrder';
        $nonce = (int)(microtime(true) * 1e6);
        $sig = hash_hmac('sha256', $nonce . $UserId . $APIKey, $APISecret);
        $headers = array();
        $params = array();

        //Adds Authenticaion vairables to params
        $params['APIKey'] = $APIKey;
        $params['Nonce'] = $nonce;
        $params['Signature'] = $sig;
        $params['OrderId'] = $order_id;

        if ($API_Key_Request == $APIKey) {
            $curl = curl_init();

            $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $url
            );
      
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            array_merge($curl_options, array(CURLOPT_POST => 1));
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));

            curl_setopt_array($curl, $curl_options);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
            $response  = json_decode(curl_exec($curl), true);

            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if($http_status != 200) {
                http_response_code(400);
            }
            else {
                switch ($response["OrderStatus"]) {
                    case 0:
                        $cc_order_status = 'COINCORNER_PENDING';
                        break;
                    case 1:
                        $cc_order_status = 'COINCORNER_PENDINGCONFIRMATION';
                        break;
                    case 2:
                        $cc_order_status = 'COINCORNER_COMPLETE';
                        break;
                    case -1:
                        $cc_order_status = 'PS_OS_CANCELED';
                        break;
                    case -2:
                        $cc_order_status = 'PS_OS_CANCELED';
                        break;
                    case -3:
                        $cc_order_status = 'PS_OS_CANCELED';
                        break;
                    case -4:
                        $cc_order_status = 'COINCORNER_PENDINGREFUND';
                        break;
                    case -5:
                        $cc_order_status = 'COINCORNER_REFUNDED';
                        break;
                    default:
                            $cc_order_status = false;
                }
            
                if ($cc_order_status !== false) {
                      $history = new OrderHistory();
                      $history->id_order = $order->id;
                      $history->changeIdOrderState(Configuration::get($cc_order_status), $order);
                      $history->addWithemail();
    
                      $this->context->smarty->assign(array(
                      'text' => 'OK'
                      ));
                }
            }

            if (_PS_VERSION_ >= '1.7') {
                $this->setTemplate('module:coincorner/views/templates/front/payment_callback.tpl');
            } else {
                $this->setTemplate('payment_callback.tpl');
            }
            
        }
    }
}
