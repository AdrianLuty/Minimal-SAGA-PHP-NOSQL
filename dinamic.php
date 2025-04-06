<?php
include 'index.php';
$result = [];

foreach ($product as $id => $info) {
    $result[] = [
        'id' => $id,
        'title' => $info['title'],
        'description' => $info['description'],
        'available' => $info['available'],
    ];
}

header('Content-Type: application/json');
echo json_encode($result);
