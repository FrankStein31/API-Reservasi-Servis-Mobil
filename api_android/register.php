<?php
require_once 'config/connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi']);
    exit;
}

// Cek email sudah terdaftar
$query = "SELECT id FROM customers WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $data['email']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar']);
    exit;
}

// Cek username sudah terdaftar
$query = "SELECT id FROM customers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $data['username']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username sudah terdaftar']);
    exit;
}

// Hash password
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

// Set nilai default untuk field opsional
$gender = $data['gender'] ?? 'M';
$phone = $data['phone'] ?? null;
$address = $data['address'] ?? null;

// Insert customer baru
$query = "INSERT INTO customers (name, username, email, password, gender, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('sssssss',
    $data['name'],
    $data['username'],
    $data['email'],
    $hashedPassword,
    $gender,
    $phone,
    $address
);

if ($stmt->execute()) {
    $customerId = $conn->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi berhasil',
        'data' => [
            'id' => $customerId,
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal melakukan registrasi']);
}

$conn->close();
?> 