<?php
session_start();
session_unset(); //This removes previous user sessions (can delete this line if you need)

if (!isset($_SESSION['client_id'])) {
    $_SESSION['client_id'] = 23;
}

echo json_encode(['client_id' => $_SESSION['client_id']]);
?>
