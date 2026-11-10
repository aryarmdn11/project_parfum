<?php
// Konfigurasi default untuk XAMPP lokal.
// Jika MySQL memakai password/port khusus, ubah nilai di bawah ini.
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'atea_parfums';
const DB_USER = 'root';
const DB_PASS = '';

const APP_TIMEZONE = 'Asia/Jakarta';

const PAYMENT_INSTRUCTIONS = [
    'Transfer Bank' => 'Transfer ke rekening toko, lalu kirim bukti pembayaran ke WhatsApp admin.',
    'QRIS'          => 'Scan QRIS toko, lalu kirim bukti pembayaran ke WhatsApp admin.',
    'E-Wallet'      => 'Kirim pembayaran ke akun e-wallet toko, lalu kirim bukti ke WhatsApp admin.',
];

const WHATSAPP_NUMBER = '';
