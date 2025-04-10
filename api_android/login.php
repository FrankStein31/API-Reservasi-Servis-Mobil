<?php
require_once 'config/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email dan password diperlukan']);
    exit;
}

$query = "SELECT id, name, username, email, password FROM customers WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $data['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
    if (password_verify($data['password'], $customer['password'])) {
        unset($customer['password']); // Hapus password dari response
        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => $customer
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Password salah']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Email tidak ditemukan']);
}

$conn->close();
?> 