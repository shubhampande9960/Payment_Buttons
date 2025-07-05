<?php
// Configuration
$entityId = '8ac7a4c79394bdc801939736f17e063d';
$bearerToken = 'OGFjN2E0Yzc5Mzk0YmRjODAxOTM5NzM2ZjFhNzA2NDF8enlac1lYckc4QXk6bjYzI1NHNng=';
$mode = $_GET['mode'] ?? null;

if ($mode === 'checkout') {
    // Step 1: Create Checkout ID
    $url = "https://eu-test.oppwa.com/v1/checkouts";
    $data = [
        'entityId' => $entityId,
        'amount' => '10.00',
        'currency' => 'EUR',
        'paymentType' => 'DB',
        'merchantTransactionId' => uniqid("tx-"),
        'paymentBrand' => 'GOOGLEPAY'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $bearerToken",
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die("Error: " . curl_error($ch));
    }
    curl_close($ch);

    $result = json_decode($response, true);
    $checkoutId = $result['id'] ?? null;

    if ($checkoutId) {
        header("Location: ?checkoutId=$checkoutId");
        exit;
    } else {
        echo "<pre>"; print_r($result); echo "</pre>";
        exit;
    }
}

if (isset($_GET['id'])) {
    // Step 3: Check payment status
    $checkoutId = $_GET['id'];
    $url = "https://eu-test.oppwa.com/v1/checkouts/$checkoutId/payment?entityId=$entityId";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $bearerToken"
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die("Error: " . curl_error($ch));
    }
    curl_close($ch);

    $result = json_decode($response, true);
    echo "<h2>Payment Status</h2><pre>";
    print_r($result);
    echo "</pre><a href='?mode=checkout'>Start New Payment</a>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Google Pay Integration (HyperPay)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            padding: 40px;
            text-align: center;
        }
        h2 {
            color: #444;
        }
        .pay-btn {
            padding: 15px 25px;
            background: #5c6bc0;
            color: #fff;
            font-size: 16px;
            border: none;
            cursor: pointer;
            margin: 30px auto;
        }
    </style>
    <?php if (isset($_GET['checkoutId'])): ?>
        <script src="https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId=<?= htmlspecialchars($_GET['checkoutId']) ?>"></script>
    <?php endif; ?>
</head>
<body>

<?php if (!isset($_GET['checkoutId'])): ?>
    <h2>Buy using Google Pay</h2>
    <form method="get">
        <input type="hidden" name="mode" value="checkout" />
        <button class="pay-btn">Start Google Pay</button>
    </form>
<?php else: ?>
    <h2>Pay Now with Google Pay</h2>
    <form action="?id=<?= htmlspecialchars($_GET['checkoutId']) ?>" class="paymentWidgets" data-brands="GOOGLEPAY"></form>
<?php endif; ?>

</body>
</html>
