<?php

#[AllowDynamicProperties]
class pm_qris
{
    public $id = __CLASS__;
    public $name = 'QRIS';
    public $description = 'By using QRIS, you can pay this order using different banks, e-wallets and much more to ease the payment process';
    public $hint = 'Please transfer the exact payment amount to this QRIS code using your banking / e-wallet application of choice';
    public $author = 'Lucky Akbar';
    public $version = '1.0';
    public $support_link = 'https://0ad.xyz';
    public $website = 'https://0ad.xyz';
    public $priority = 0;

    public function __construct()
    {
    }

    private function getName()
    {
        if (!empty($this->settings['name'])) {
            return $this->settings['name'];
        }

        return $this->name;
    }

    private function getDescription()
    {
        if (!empty($this->settings['description'])) {
            return $this->settings['description'];
        }

        return $this->description;
    }

    private function getHint()
    {
        if (!empty($this->settings['payment_hint'])) {
            return $this->settings['payment_hint'];
        }

        return $this->hint;
    }

    private function pre_transfer($order)
    {
        $order->data['order_status_id'] = $this->settings['initial_order_status_id'];
        $order->save();

        cart::reset();
    }

    public function transfer($order)
    {

        $this->pre_transfer($order);

        document::$layout = 'default';
        document::$snippets['title'][] = $this->getName();
        document::$snippets['description'] = $this->getDescription();

        $qrisPage = new ent_view();

        $qrisPage->snippets = [
            'title' => $this->getName(),
            'hint' => $this->getHint(),
            'expected_amount' => 'Amount expected: ' . currency::format($order->data['payment_due'], true, $order->data['currency_code'], $order->data['currency_value']),
            'image_path' => $this->settings['qris_qr_code'],
            'render_submit_btn' => true,
        ];

        $html = $qrisPage->stitch('views/qris');

        return [
            'method' => 'html',
            'content' => $html,
        ];
    }

    function settings()
    {
        return [
            [
                'key' => 'status',
                'default_value' => '1',
                'title' => language::translate(__CLASS__ . ':title_status', 'Status'),
                'description' => language::translate(__CLASS__ . ':description_status', 'Enables or disables the module.'),
                'function' => 'toggle("e/d")',
            ],
            [
                'key' => 'icon',
                'default_value' => '',
                'title' => language::translate(__CLASS__ . ':title_icon', 'Icon'),
                'description' => language::translate(__CLASS__ . ':description_icon', 'Payment icon'),
                'function' => 'text()',
            ],
            [
                'key' => 'fee',
                'default_value' => '0',
                'title' => language::translate(__CLASS__ . ':title_payment_fee', 'Payment Fee'),
                'description' => language::translate(__CLASS__ . ':description_payment_fee', 'Adds a payment fee to the order.'),
                'function' => 'decimal()',
            ],
            [
                'key' => 'qris_qr_code',
                'default_value' => '',
                'title' => language::translate(__CLASS__ . ':title_qris_qr_code', 'QRIS qr code'),
                'description' => language::translate(__CLASS__ . ':description_qris_qr_code', 'Your actual QRIS code to be scanned by customer'),
                'function' => 'text()',
            ],
            [
                'key' => 'tax_class_id',
                'default_value' => '',
                'title' => language::translate(__CLASS__ . ':title_tax_class', 'Tax Class'),
                'description' => language::translate(__CLASS__ . ':description_tax_class', 'The tax class for the fee.'),
                'function' => 'tax_class()',
            ],
            [
                'key' => 'initial_order_status_id',
                'default_value' => '0',
                'title' => language::translate('initial_order_status', 'Initial Order Status'),
                'description' => language::translate('modules:description_qris_payment_initial_order_status', 'When user create the order with this payment option, give the initial saved order with this status'),
                'function' => 'order_status()',
            ],
            [
                'key' => 'after_payment_order_status_id',
                'default_value' => '0',
                'title' => language::translate('after_payment_order_status', 'After Payment Order Status'),
                'description' => language::translate('modules:description_qris_payment__after_payment_order_status', 'After the user being shown the QRIS & hopefully paid the order, update the order with this status'),
                'function' => 'order_status()',
            ],
            [
                'key' => 'geo_zones',
                'default_value' => '',
                'title' => language::translate('title_geo_zone_limitation', 'Geo Zone Limitation'),
                'description' => language::translate('modules:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise, leave it blank.'),
                'function' => 'geo_zone()',
            ],
            [
                'key' => 'description',
                'default_value' => $this->description,
                'title' => language::translate('qris_payment_description', 'Description'),
                'description' => language::translate('modules:qris_payment_description', 'Fill this value to use custom description, otherwise left default'),
                'function' => 'text()',
            ],
            [
                'key' => 'payment_hint',
                'default_value' => $this->hint,
                'title' => language::translate('qris_payment_hint', 'Payment Hint'),
                'description' => language::translate('modules:qris_payment_hint', 'Short hint to tell customer how to pay using QRIS, fill to customize or left default'),
                'function' => 'text()',
            ],
            [
                'key' => 'priority',
                'default_value' => '0',
                'title' => language::translate('qris_payment_priority', 'Priority'),
                'description' => language::translate('modules:qris_payment_priority', 'Process this module in the given priority order.'),
                'function' => 'number()',
            ],
        ];
    }

    public function options($items, $subtotal, $tax, $currency_code, $customer)
    {
        if (empty($this->settings['status'])) return;

        $country_code = !empty($customer['shipping_address']['country_code']) ? $customer['shipping_address']['country_code'] : $customer['country_code'];

        if (!empty($this->settings['geo_zones'])) {
            if (!reference::country($country_code)->in_geo_zone($this->settings['geo_zones'], $customer)) return;
        }

        $method = [
            'title' => $this->getName(),
            'description' => $this->getDescription(),
            'options' => [
                [
                    'id' => 'qris',
                    'icon' => $this->settings['icon'],
                    'name' => $this->getName(),
                    'description' => $this->getDescription(),
                    'fields' => '',
                    'cost' => $this->settings['fee'],
                    'tax_class_id' => $this->settings['tax_class_id'],
                    'confirm' => language::translate(__CLASS__ . ':title_confirm_order', 'Create Order'),
                ],
            ]
        ];
        return $method;
    }

    public function verify($order)
    {
        $order_id = $order->data['id'];
        $qris_transaction_id = 'qris-' . $order_id;
        $comments = 'This transaction was made using QRIS payment method. Please manually double check to ensure this order has been paid';

        $order->data['comments'][] = [
            'author' => 'System',
            'text' => $comments,
            'hidden' => false,
        ];

        $order->save();

        return [
            'order_status_id' => $this->settings['after_payment_order_status_id'],
            'transaction_id' => $qris_transaction_id,
            'payment_terms' => 'PIA',
        ];
    }

    public function install()
    {
    }

    public function uninstall()
    {
    }
}
