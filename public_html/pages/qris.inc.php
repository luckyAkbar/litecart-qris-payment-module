<?php

document::$layout = 'default';
document::$snippets['title'][] = settings::get('store_name') . 'QRIS Code';
document::$snippets['description'] = settings::get('store_name') . 'QRIS Code';

$qrisPage = new ent_view();

$qrisPage->snippets = [
    'title' => settings::get('store_name') . ' QRIS Code',
    'image_path' => 'images/payment/qris-qr-code.png',
    'render_submit_btn' => false,
];

echo $qrisPage->stitch('views/qris');
