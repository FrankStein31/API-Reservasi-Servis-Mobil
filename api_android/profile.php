<?php
require_once 'config/connection.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (!isset($_GET['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID customer diperlukan']);
            exit;
        }

        $customerId = $_GET['id'];
        $query = "SELECT id, name, username, email, gender, phone, address FROM customers WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $customerId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $customer]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Customer tidak ditemukan']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID customer diperlukan']);
            exit;
        }

        // Cek email sudah digunakan
        if (isset($data['email'])) {
            $query = "SELECT id FROM customers WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $data['email'], $data['id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan']);
                exit;
            }
        }

        // Update data customer
        $fields = [];
        $types = '';
        $values = [];
        
        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $types .= 's';
            $values[] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $types .= 's';
            $values[] = $data['email'];
        }
        if (isset($data['gender'])) {
            $fields[] = 'gender = ?';
            $types .= 's';
            $values[] = $data['gender'];
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = ?';
            $types .= 's';
            $values[] = $data['phone'];
        }
        if (isset($data['address'])) {
            $fields[] = 'address = ?';
            $types .= 's';
            $values[] = $data['address'];
        }
        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $types .= 's';
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diupdate']);
            exit;
        }

        $values[] = $data['id'];
        $types .= 'i';

        $query = "UPDATE customers SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            // Ambil data terbaru
            $query = "SELECT id, name, username, email, gender, phone, address FROM customers WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $data['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();

            echo json_encode([
                'status' => 'success',
                'message' => 'Profile berhasil diupdate',
                'data' => $customer
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal update profile']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
        break;
}

$conn->close(); 