<?php
require_once 'config/connection.php';

// Set header untuk response JSON
header('Content-Type: application/json');

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $customer_id = $_GET['customer_id'] ?? null;
        
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Customer ID diperlukan']);
            exit;
        }

        $query = "SELECT * FROM vehicles WHERE customer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehicles = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $vehicles]);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "INSERT INTO vehicles (customer_id, name, brand, year, plate_number, chassis_number) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssss', 
            $data['customer_id'],
            $data['name'],
            $data['brand'],
            $data['year'],
            $data['plate_number'],
            $data['chassis_number']
        );

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Kendaraan berhasil ditambahkan']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan kendaraan']);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "UPDATE vehicles SET name = ?, brand = ?, year = ?, plate_number = ?, chassis_number = ? WHERE id = ? AND customer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssssii',
            $data['name'],
            $data['brand'],
            $data['year'],
            $data['plate_number'],
            $data['chassis_number'],
            $data['id'],
            $data['customer_id']
        );

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Kendaraan berhasil diperbarui']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui kendaraan']);
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "DELETE FROM vehicles WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $data['id']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Kendaraan berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus kendaraan']);
        }
        break;
        
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Method tidak diizinkan',
            'data' => null
        ]);
        break;
}

$conn->close();
?> 