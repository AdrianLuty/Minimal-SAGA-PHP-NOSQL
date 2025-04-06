<?php
session_start();
$client = $_SESSION['client_id'] ?? null;

if ($client) {
    echo "Cart for client $client has been released.";
} else {
    echo "Client not identified.";
}
?>
