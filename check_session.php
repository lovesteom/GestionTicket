<?php
session_start();
header('Content-Type: application/json');

$response = [
    'is_logged_in' => isset($_SESSION['user_id'])
];

echo json_encode($response);