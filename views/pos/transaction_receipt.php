<?php
require_once __DIR__ . '/../../configs/database.php';
date_default_timezone_set('Asia/Manila');

// Validate transaction ID
$transactionId = $_GET['id'] ?? null;
if (empty($transactionId)) {
    die('Transaction ID is required');
}

// Get transaction details from database
$transactionQuery = "
    SELECT 
        t.transaction_id,
        t.transaction_date,
        t.subtotal,
        t.vat,
        t.total,
        t.discount_type,
        t.discount_amount,
        t.payment_method,
        t.amount_tendered,
        t.change_amount,
        u.username as cashier_name
    FROM transactions t
    LEFT JOIN users u ON t.cashier_id = u.user_id
    WHERE t.transaction_id = :transaction_id
";

$stmt = $pdo->prepare($transactionQuery);
$stmt->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
$stmt->execute();
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    die("Transaction not found with ID: $transactionId");
}


// Get transaction items
$itemsQuery = "
    SELECT 
        product_name,
        quantity,
        price
    FROM transaction_items
    WHERE transaction_id = :transaction_id
";

$stmt = $pdo->prepare($itemsQuery);
$stmt->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate receipt values
$totalBeforeDiscount = (float)$transaction['subtotal'];
$discountAmount = 0;
$discountPercentage = 0;

if ($transaction['discount_type'] !== 'none' && $transaction['discount_amount'] > 0) {

    if($transaction["discount_type"] === "employee"){
        $discountPercentage = 25;
    } else if($transaction["discount_type"] === "senior"){
        $discountPercentage = 20;
    } else {
        $totalPrice = $transaction["subtotal"] + $transaction["vat"];
        $discountPercentage = ($transaction["discount_amount"] / $totalPrice) * 100;
    }
    $discountAmount = $totalBeforeDiscount * ($discountPercentage / 100);
}

// Format date and time
$transactionDate = new DateTime($transaction['transaction_date']);
$date = $transactionDate->format('F j, Y');
$time = $transactionDate->format('g:i A');

// Map discount types to friendly names
$discountNames = [
    'senior' => 'Senior Citizen',
    'student' => 'Student',
    'pwd' => 'PWD',
    'employee' => 'Employee',
    'custom' => 'Custom'
];

$discountType = $transaction['discount_type'];
if (isset($discountNames[$transaction['discount_type']])) {
    $discountType = $discountNames[$transaction['discount_type']];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?= htmlspecialchars($transaction['transaction_id']) ?></title>
    <link rel="stylesheet" href="../../public/css/transaction_receipt.css">
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <img src="../../public/images/the_lagoon_logo.png">
            <h1>The Lagoon Resort Finland, Inc.</h1>
            <p>#62 Midway Baloy Beach, Barretto<br>Olongapo City</p>
            <p>Phone #: 09173190412</p>
        </div>
        
        <div class="receipt-details">
            <p><strong>Receipt:</strong> #<?= htmlspecialchars($transaction['transaction_id']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($time) ?></p>
            <p><strong>Cashier:</strong> <?= htmlspecialchars($transaction['cashier_name'] ?? 'System') ?></p>
        </div>
        
        <table class="receipt-items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td>₱<?= number_format($item['price'], 2) ?></td>
                        <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <table class="receipt-totals">
            <tr>
                <td>Subtotal:</td>
                <td>₱<?= number_format($transaction['subtotal'], 2) ?></td>
            </tr>
            <tr>
                <td>VAT (1.12%):</td>
                <td>₱<?= number_format($transaction['vat'], 2) ?></td>
            </tr>
            <tr>
                <td>Total Before Discount:</td>
                <td>₱<?= number_format($totalBeforeDiscount, 2) ?></td>
            </tr>
            <?php if ($discountPercentage > 0): ?>
            <tr class="discount-row">
                <td><?= htmlspecialchars($discountType) ?> Discount (<?= $discountPercentage ?>%):</td>
                <td>-₱<?= number_format($discountAmount, 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td>FINAL TOTAL:</td>
                <td>₱<?= number_format($transaction['total'], 2) ?></td>
            </tr>
            <tr>
                <td>Amount Tendered:</td>
                <td>₱<?= number_format($transaction['amount_tendered'], 2) ?></td>
            </tr>
            <tr>
                <td>Change:</td>
                <td>₱<?= number_format($transaction['change_amount'], 2) ?></td>
            </tr>
        </table>
        
        <div class="receipt-footer">
            <p>Thank you for your purchase!</p>
            <p>Tax ID: 4990005-42231443-003821</p>
            <p>------------------------------</p>
        </div>
        
        <button class="print-button" onclick="window.print()">Print Receipt</button>
    </div>
</body>
</html>