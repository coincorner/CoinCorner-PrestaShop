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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Coincorner extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'coincorner';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.1';
        $this->author = 'CoinCorner';
        $this->controllers = 'callback, cancel, payment, redirect';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Bitcoin via CoinCorner');
        $this->description = $this->l('Accept Bitcoin through your business with CoinCorner.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $order_pending = new OrderState();
        $order_pending->name = array_fill(0, 10, 'Pending');
        $order_pending->send_email = 0;
        $order_pending->invoice = 0;
        $order_pending->color = 'RoyalBlue';
        $order_pending->unremovable = false;
        $order_pending->add();

        $order_pendingconfirmation = new OrderState();
        $order_pendingconfirmation->name = array_fill(0, 10, 'Pending Confirmation');
        $order_pendingconfirmation->send_email = 0;
        $order_pendingconfirmation->invoice = 0;
        $order_pendingconfirmation->color = '#DC143C';
        $order_pendingconfirmation->unremovable = false;
        $order_pendingconfirmation->add();

        $order_complete = new OrderState();
        $order_complete->name = array_fill(0, 10, 'Complete');
        $order_complete->send_email = 0;
        $order_complete->invoice = 0;
        $order_complete->color = '#d9ff94';
        $order_complete->unremovable = false;
        $order_complete->add();

        $order_cancelled = new OrderState();
        $order_cancelled->name = array_fill(0, 10, 'Cancelled');
        $order_cancelled->color = '#8f0621';
        $order_cancelled->unremovable = false;
        $order_cancelled->add();

        $order_expired = new OrderState();
        $order_expired->name = array_fill(0, 10, 'Expired');
        $order_expired->color = '#8f0621';
        $order_expired->unremovable = false;
        $order_expired->add();

        $order_pendingrefund = new OrderState();
        $order_pendingrefund->name = array_fill(0, 10, 'Pending Refund');
        $order_pendingrefund->color = '#8f0621';
        $order_pendingrefund->unremovable = false;
        $order_pendingrefund->add();

        $order_Refunded = new OrderState();
        $order_Refunded->name = array_fill(0, 10, 'Refunded');
        $order_Refunded->color = '#8f0621';
        $order_Refunded->unremovable = false;
        $order_Refunded->add();


        Configuration::updateValue('COINCORNER_PENDING', $order_pending->id);
        Configuration::updateValue('COINCORNER_PENDINGCONFIRMATION', $order_pendingconfirmation->id);
        Configuration::updateValue('COINCORNER_COMPLETE', $order_complete->id);
        Configuration::updateValue('COINCORNER_CANCELLED', $order_cancelled->id);
        Configuration::updateValue('COINCORNER_EXPIRED', $order_expired->id);
        Configuration::updateValue('COINCORNER_PENDINGREFUND', $order_pendingrefund->id);
        Configuration::updateValue('COINCORNER_REFUNDED', $order_Refunded->id);
        Configuration::updateValue('COINCORNER_INVOICE_CURRENCY_DEFAULT', 'GBP');
        Configuration::updateValue('COINCORNER_SETTLE_CURRENCY_DEFAULT', 'GBP');
 

        if (!parent::install()
        || !$this->registerHook('payment')
        || !$this->registerHook('displayPaymentEU')
        || !$this->registerHook('paymentReturn')
        || !$this->registerHook('paymentOptions')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        return (
            Configuration::deleteByName('MYMODULE_NAME') &&
            Configuration::deleteByName('COINCORNER_PENDING') &&
            Configuration::deleteByName('COINCORNER_PENDINGCONFIRMATION') &&
            Configuration::deleteByName('COINCORNER_COMPLETE') &&
            Configuration::deleteByName('COINCORNER_CANCELLED') &&
            Configuration::deleteByName('COINCORNER_EXPIRED') &&
            Configuration::deleteByName('COINCORNER_REFUNDED') &&
            Configuration::deleteByName('COINCORNER_PENDINGREFUND') &&
            Configuration::deleteByName('COINCORNER_API_KEY') &&
            Configuration::deleteByName('COINCORNER_API_SECRET') &&
            Configuration::deleteByName('COINCORNER_USER_ID') &&
            Configuration::deleteByName('COINCORNER_INVOICE_CURRENCY') &&
            Configuration::deleteByName('COINCORNER_SETTLE_CURRENCY') &&
            Configuration::deleteByName('COINCORNER_INVOICE_CURRENCY_DEFAULT') &&
            Configuration::deleteByName('COINCORNER_SETTLE_CURRENCY_DEFAULT') &&
            parent::uninstall()
        );
    }


    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('COINCORNER_API_KEY', Tools::getValue('COINCORNER_API_KEY'));
            Configuration::updateValue('COINCORNER_API_SECRET', Tools::getValue('COINCORNER_API_SECRET'));
            Configuration::updateValue('COINCORNER_USER_ID', Tools::getValue('COINCORNER_USER_ID'));
            Configuration::updateValue('COINCORNER_INVOICE_CURRENCY_DEFAULT', Tools::getValue('COINCORNER_INVOICE_CURRENCY'));
            Configuration::updateValue('COINCORNER_SETTLE_CURRENCY_DEFAULT', Tools::getValue('COINCORNER_SETTLE_CURRENCY'));
        }
        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }


    private function displayCoincorner()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    private function displayCoincornerInformation($renderForm)
    {
        $this->html .= $this->displayCoincorner();
        $this->context->controller->addCSS($this->_path . '/views/css/tabs.css', 'all');
        $this->context->controller->addJS($this->_path . '/views/js/javascript.js', 'all');
        $this->context->smarty->assign('form', $renderForm);
        return $this->display(__FILE__, 'information.tpl');
    }


    public function getContent()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            $API_Key = Tools::getValue('COINCORNER_API_KEY');
            $API_Secret = Tools::getValue('COINCORNER_API_SECRET');
            $UserId = Tools::getValue('COINCORNER_USER_ID');

            Configuration::updateValue('COINCORNER_API_KEY', $API_Key);
            Configuration::updateValue('COINCORNER_API_SECRET', $API_Secret);
            Configuration::updateValue('COINCORNER_USER_ID', $UserId);
            Configuration::updateValue('COINCORNER_INVOICE_CURRENCY_DEFAULT', Tools::getValue('COINCORNER_INVOICE_CURRENCY'));
            Configuration::updateValue('COINCORNER_SETTLE_CURRENCY_DEFAULT', Tools::getValue('COINCORNER_SETTLE_CURRENCY'));
        }

        $renderForm = $this->renderForm();
        $this->html .= $this->displayCoincornerInformation($renderForm);

        return $this->html;
    }


    public function renderForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm = array(
            'form' => array(
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
            
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'name' => 'COINCORNER_API_KEY',
                        'size' => 20,
                        'required' => true
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('API Secret'),
                        'name' => 'COINCORNER_API_SECRET',
                        'size' => 20,
                        'required' => true
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('UserId'),
                        'name' => 'COINCORNER_USER_ID',
                        'size' => 20,
                        'required' => true
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Invoice Currency'),
                        'name' => 'COINCORNER_INVOICE_CURRENCY',
                        'size' => 20,
                        'required' => true
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Settle Currency'),
                        'name' => 'COINCORNER_SETTLE_CURRENCY',
                        'size' => 20,
                        'required' => true
                    ]
                ],
            
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ),
        
        );
            
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;
        $this->fieldsForm = array();
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

    
        $helper->fields_value['COINCORNER_INVOICE_CURRENCY'] = Configuration::get('COINCORNER_INVOICE_CURRENCY_DEFAULT');
        $helper->fields_value['COINCORNER_SETTLE_CURRENCY'] = Configuration::get('COINCORNER_SETTLE_CURRENCY_DEFAULT');
        $helper->fields_value['COINCORNER_API_KEY'] = Configuration::get('COINCORNER_API_KEY');
        $helper->fields_value['COINCORNER_API_SECRET'] = Configuration::get('COINCORNER_API_SECRET');
        $helper->fields_value['COINCORNER_USER_ID'] = Configuration::get('COINCORNER_USER_ID');

        return $helper->generateForm(array($fieldsForm));
    }

    public function hookPaymentOptions()
    {
        if (!$this->active) {
            return;
        }

        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setCallToActionText('Bitcoin via CoinCorner')
            ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true))
            ->setAdditionalInformation(
                $this->context->smarty->fetch('module:coincorner/views/templates/hook/coincorner_intro.tpl')
            );

        $payment_options = array($newOption);

        return $payment_options;
    }

    public function hookPayment($params)
    {
        if (_PS_VERSION_ >= 1.7) {
            return;
        }
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $this->smarty->assign(array(
        'this_path'     => $this->_path,
        'this_path_bw'  => $this->_path,
        'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (_PS_VERSION_ <= 1.7) {
            return;
        }

        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
        ));

        return $this->context->smarty->fetch(__FILE__, 'payment.tpl');
    }


    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        if (_PS_VERSION_ < 1.7) {
            $order = $params['objOrder'];
            $state = $order->current_state;
        } else {
            $state = $params['order']->getCurrentState();
        }
        $this->smarty->assign(array(
            'state' => $state,
            'paid_state' => (int)Configuration::get('PS_OS_PAYMENT'),
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
        ));
        return $this->display(__FILE__, 'payment_return.tpl');
    }
}
