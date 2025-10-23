<?php
require_once __DIR__ . '/../../controllers/posController.php';
// Add this at the top of receipt_template.php, after the require statements
date_default_timezone_set('Asia/Manila'); // Assuming you're in the Philippines



// Get transaction data from URL parameters
$transaction = [
    'transactionId' => $_GET['transaction_id'] ?? '',
    'cashierName' => $_GET['cashier_name'] ?? '',
    'transactionDate' => $_GET['transaction_date'] ?? '',
    'items' => isset($_GET['items']) ? json_decode(urldecode($_GET['items']), true) : [],
    'subtotal' => (float)($_GET['subtotal'] ?? 0),
    'vat' => (float)($_GET['vat'] ?? 0),
    'total' => (float)($_GET['total'] ?? 0),
    'method' => $_GET['payment_method'] ?? 'cash',
    'amountTendered' => (float)($_GET['amount_tendered'] ?? 0),
    'change' => (float)($_GET['change_amount'] ?? 0),
    'discountType' => $_GET['discount_type'] ?? 'none',
    'discountPercentage' => (float)($_GET['discount_percentage'] ?? 0),
    'discountAmount' => (float)($_GET['discount_amount'] ?? 0)
];
// Calculate discount information
$hasDiscount = ($transaction['discountType'] !== 'none' && $transaction['discountPercentage'] > 0);

// Map discount types to friendly names
$discountNames = [
    'senior' => 'Senior Citizen',
    'student' => 'Student',
    'pwd' => 'PWD',
    'employee' => 'Employee',
    'custom' => 'Custom'
];

$discountType = $transaction['discountType'];
if (isset($discountNames[$transaction['discountType']])) {
    $discountType = $discountNames[$transaction['discountType']];
}

// Calculate totals for display
$subtotal = $transaction['subtotal'];
$vatAmount = $transaction['vat'];
$totalBeforeDiscount = $subtotal;
$discountAmount = $transaction['discountAmount'];
$finalTotal = $transaction["subtotal"];

// Verify calculations match
if ($hasDiscount && abs($finalTotal - ($totalBeforeDiscount - $discountAmount)) > 0.01) {
    // Recalculate if there's a mismatch (floating point precision issue)
    $discountAmount = $totalBeforeDiscount * ($transaction['discountPercentage'] / 100);
    $finalTotal = $totalBeforeDiscount - $discountAmount;
}

$transactionDateTime = new DateTime($transaction['transactionDate']);
$date = $transactionDateTime->format('F j, Y');
$time = $transactionDateTime->format('g:i A');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo htmlspecialchars($transaction['transactionId']); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 10px;
            padding: 0;
            font-size: 12px;
            max-width: 300px;
            margin: 0 auto;
        }
        .receipt-container {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }
        .receipt-header h1 {
            font-size: 16px;
            margin: 5px 0;
        }
        .receipt-header img {
            max-width: 80px;
            height: auto;
        }
        .receipt-header p {
            margin: 3px 0;
            font-size: 11px;
        }
        .receipt-details {
            margin-bottom: 10px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            font-size: 11px;
        }
        .receipt-details p {
            margin: 2px 0;
            width: 48%;
        }
        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11px;
        }
        .receipt-items th {
            text-align: left;
            border-bottom: 1px solid #ddd;
            padding: 4px;
            font-size: 11px;
        }
        .receipt-items td {
            padding: 4px;
            border-bottom: 1px dotted #eee;
        }
        .receipt-items td:nth-child(2),
        .receipt-items td:nth-child(3),
        .receipt-items td:nth-child(4) {
            text-align: right;
        }
        .receipt-totals {
            width: 100%;
            margin: 10px 0;
            font-size: 11px;
        }
        .receipt-totals tr td:first-child {
            text-align: left;
        }
        .receipt-totals tr td:last-child {
            text-align: right;
        }
        .receipt-totals td {
            padding: 3px 0;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }
        /* .discount-row {
            color: #d9534f;
        } */
        .payment-info {
            margin-top: 5px;
            border-top: 1px dotted #eee;
            padding-top: 5px;
        }
        .receipt-footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        .print-button {
            display: block;
            margin: 15px auto 5px;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background-color: #45a049;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
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
            <p><strong>Receipt:</strong> #<?php echo htmlspecialchars($transaction['transactionId']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($date); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($time); ?></p>
            <p><strong>Cashier:</strong> <?php echo htmlspecialchars($transaction['cashierName']); ?></p>
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
<!-- In receipt_template.php, update the items loop -->
<?php foreach ($transaction['items'] as $item): ?>
    <tr>
        <td>
            <?php echo htmlspecialchars($item['product_name']); ?>
            <?php if (isset($item['unit_name']) && !$item['is_primary']): ?>
                <small>(<?php echo htmlspecialchars($item['unit_name']); ?>)</small>
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
        <td>₱<?php echo number_format($item['price'], 2); ?></td>
        <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
    </tr>
<?php endforeach; ?>
            </tbody>
        </table>
        
        <table class="receipt-totals">
            <t>
                <td>Subtotal:</td>
                <td>₱<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
                <td>VAT (1.12%):</td>
                <td>₱<?php echo number_format($transaction['vat'], 2); ?></td>
            </tr>
            <tr>
                <td>Total Before Discount:</td>
                <td>₱<?php echo number_format($totalBeforeDiscount, 2); ?></td>
            </tr>
            <?php if ($hasDiscount): ?>
            <tr class="discount-row">
                <td><?php echo htmlspecialchars($discountType); ?> Discount (<?php echo $transaction['discountPercentage']; ?>%):</td>
                <td>-₱<?php echo number_format($discountAmount, 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td>FINAL TOTAL:</td>
                <td>₱<?php echo number_format($finalTotal, 2); ?></td>
            </tr>
            <tr>
                <td>Amount Tendered:</td>
                <td>₱<?php echo number_format($transaction['amountTendered'], 2); ?></td>
            </tr>
            <tr>
                <td>Change:</td>
                <td>₱<?php echo number_format($transaction["amountTendered"] - $finalTotal, 2); ?></td>
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