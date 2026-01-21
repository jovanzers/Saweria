<?php // https://github.com/jovanzers/Saweria
if (empty($_GET['oid'])) {
    echo '<a href="https://github.com/jovanzers/Saweria">How to use?</a><hr>';
    echo 'ZERS was here!<br>With ❤️ by WinTen Dev';
    exit();
}

$oid = $_GET['oid'];
$url = $oid;
if (strpos($oid, 'saweria.co') === false) {
    $url = 'https://saweria.co/receipt/' . $oid;
}
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Referer: https://saweria.co',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36'
    ]
]);
$response = curl_exec($ch);
curl_close($ch);
// echo $response;
$dom = new DOMDocument();
@$dom->loadHTML($response);

$xpath = new DOMXPath($dom);
$orderId = $xpath->query('//*[@id="__next"]/div/div/div[5]/input');
$orderDate = $xpath->query('//*[@id="__next"]/div/div/div[3]/input');
$amount = $xpath->query('//*[@id="__next"]/div/div/h3[2]');
if (strpos($response, 'Berhasil') !== false) {
    $result = [
        'OrderId' => @$orderId->item(0)->getAttribute('value'),
        'OrderDate' => date('Y-m-d', strtotime(str_replace('Rp&nbsp;', '', @$orderDate->item(0)->getAttribute('value')))),
        'Total' => (int) preg_replace('/[^0-9]/', '', @$amount[0]->nodeValue),
        'PaymentSource' => 'Saweria'
    ];
} else {
    $result = [
        'IsValid' => false,
        'Message' => 'Transaksi tidak terdaftar atau belum terselesaikan.',
        'html' => $response
    ];
    // echo $response;
    // exit();
}

header('Content-Type: application/json');
echo json_encode($result);
