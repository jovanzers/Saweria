<?php
error_reporting(0);
header('Content-Type: application/json');

if (empty($_GET['oid'])) {
    echo json_encode([
        'IsValid' => false,
        'Message' => 'Parameter oid kosong',
        'Usage' => 'https://github.com/jovanzers/Saweria'
    ]);
    exit;
}

$oid = trim($_GET['oid']);
$url = (strpos($oid, 'saweria.co') !== false)
    ? $oid
    : 'https://saweria.co/receipt/' . $oid;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        'Referer: https://saweria.co',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/117.0.0.0'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response || $httpCode !== 200) {
    echo json_encode([
        'IsValid' => false,
        'Message' => 'Gagal mengambil data dari Saweria'
    ]);
    exit;
}

if (stripos($response, 'Berhasil') === false) {
    echo json_encode([
        'IsValid' => false,
        'Message' => 'Transaksi tidak ditemukan atau belum selesai'
    ]);
    exit;
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
@$dom->loadHTML($response);
$xpath = new DOMXPath($dom);

$inputs = $xpath->query('//input');
$orderId = null;
$orderDate = null;

foreach ($inputs as $input) {
    $val = trim($input->getAttribute('value'));

    if (!$orderId && preg_match('/^[a-f0-9\-]{20,}$/i', $val)) {
        $orderId = $val;
        continue;
    }

    if (!$orderDate && preg_match('/\d{2}[-\/]\d{2}[-\/]\d{4}/', $val)) {
        $orderDate = $val;
        continue;
    }
}

$amount = null;
$h3s = $xpath->query('//h3');

foreach ($h3s as $h3) {
    $text = trim($h3->nodeValue);
    if (preg_match('/Rp|IDR|[0-9]\./', $text)) {
        $amount = $text;
        break;
    }
}

if (!$orderId || !$orderDate || !$amount) {
    echo json_encode([
        'IsValid' => false,
        'Message' => 'Struktur halaman berubah atau data tidak lengkap'
    ]);
    exit;
}

$result = [
    'IsValid' => true,
    'OrderId' => $orderId,
    'OrderDate' => date('Y-m-d', strtotime($orderDate)),
    'Total' => (int) preg_replace('/[^0-9]/', '', $amount),
    'PaymentSource' => 'Saweria'
];

echo json_encode($result);
