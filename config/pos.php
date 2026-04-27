<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Toko
    |--------------------------------------------------------------------------
    */
    'store_name'    => env('POS_STORE_NAME', 'Toko Waris'),
    'store_address' => env('POS_STORE_ADDRESS', 'Jl. Contoh No. 1, Tasikmalaya'),
    'store_phone'   => env('POS_STORE_PHONE', '0265-xxxxxx'),

    /*
    |--------------------------------------------------------------------------
    | Pajak (PPN) dalam persen. Default 0 (tidak ada pajak).
    |--------------------------------------------------------------------------
    */
    'tax_rate' => env('POS_TAX_RATE', 0),

    /*
    |--------------------------------------------------------------------------
    | Notifikasi stok & kedaluwarsa
    |--------------------------------------------------------------------------
    */
    'expiry_days_warn' => env('POS_EXPIRY_DAYS_WARN', 30),

    /*
    |--------------------------------------------------------------------------
    | Struk
    |--------------------------------------------------------------------------
    */
    'receipt_footer' => env('POS_RECEIPT_FOOTER', 'Terima kasih atas kunjungan Anda!'),
];
