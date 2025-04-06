<?php
$data = json_decode(file_get_contents("php://input"), true);
$client = $data["client"];
$products = $data["products"];

$ticketFile = "../wishlist/tickets.php";

if (!file_exists($ticketFile)) {
    file_put_contents($ticketFile, "<?php\n");
}

include $ticketFile;

if (!isset($ticket)) {
    $ticket = [];
}

foreach ($products as $p) {
    $id = $p['id'];
    $ticket[$client][$id] = true;
}

$content = "<?php\n";
foreach ($ticket as $clientId => $clientProducts) {
    foreach ($clientProducts as $productId => $value) {
        $content .= "\$ticket['$clientId']['$productId'] = true;\n";
    }
}

file_put_contents($ticketFile, $content);

echo "Ticket generated successfully.";
