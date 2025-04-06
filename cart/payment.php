<?php
$date = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
$timestamp = $date->format("d/m/Y H:i:s");

$data = json_decode(file_get_contents("php://input"), true);
$client = $data["client"];
$products = $data["products"];
$paymentCode = $data["paymentCode"];

$salesFile = "../sales/sales.php";
$productsFile = "../products/index.php";
$clientsFile = "../clients/index.php";
$stockFile = "../products/stock.php";
$totalSalesFile = "../sales/fullreport.php";

include $productsFile;

$stockLines = [];
$totalSalesLines = [];

foreach ($products as $p) {
    $id = $p['id'];
    $quantity = $p['quantity'];

    // Register in stock.php if not unlimited
    if (isset($product[$id]['available']) && strtolower($product[$id]['available']) !== "unlimited") {
        $stockLines[] = "\$product[$id]['available'] -= $quantity;";
    }

    // Register total sales as separate increment
    $totalSalesLines[] = "\$product[$id] += $quantity;";
}

// Add lines to stock.php
if (!file_exists($stockFile)) {
    file_put_contents($stockFile, "<?php\n");
}
file_put_contents($stockFile, implode("\n", $stockLines) . "\n", FILE_APPEND);

// Add lines to fullreport.php
if (!file_exists($totalSalesFile)) {
    file_put_contents($totalSalesFile, "<?php\n");
}
file_put_contents($totalSalesFile, implode("\n", $totalSalesLines) . "\n", FILE_APPEND);

// Save sales data
foreach ($products as $p) {
    $productId = $p['id'];
    $quantity = $p['quantity'];
    $content .= "\$sold['$client']['$paymentCode']['items']['$productId']['quantity'] = $quantity;\n";
    $content .= "\$sold['$client']['$paymentCode']['items']['$productId']['amount'] = \$unitPrice;\n";
}
$content .= "\$sold['$client']['$paymentCode']['items']['data']['transaction'] = 'transaction number';\n";
$content .= "\$sold['$client']['$paymentCode']['items']['data']['date'] = '$timestamp';\n";
$content .= "\$sold['$client']['$paymentCode']['items']['data']['shipment'] = 'shipping tracking number';\n";

if (!file_exists($salesFile)) {
    file_put_contents($salesFile, "<?php\n");
}

file_put_contents($salesFile, $content, FILE_APPEND);

// Remove client from client list
if (file_exists($clientsFile)) {
    $clients = include $clientsFile;
    unset($clients[$client]);
    file_put_contents($clientsFile, '<?php return ' . var_export($clients, true) . ";\n");
}

echo "Payment registered.";
