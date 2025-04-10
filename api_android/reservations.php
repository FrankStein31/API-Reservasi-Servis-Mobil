<?php
require_once 'config/connection.php';

// Set header untuk response JSON
header('Content-Type: application/json');

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get detail reservasi berdasarkan ID
            $reservationId = $_GET['id'];
            
            $query = "SELECT r.*, v.name as vehicle_name, v.plate_number, p.name as package_name, 
                     s.id as service_id, s.status as service_status 
                     FROM reservations r 
                     LEFT JOIN vehicles v ON r.vehicle_id = v.id 
                     LEFT JOIN packages p ON r.package_id = p.id 
                     LEFT JOIN services s ON r.id = s.reservation_id 
                     WHERE r.id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $reservationId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $reservation = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $reservation]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Reservasi tidak ditemukan']);
            }
            exit;
        }
        
        $customer_id = $_GET['customer_id'] ?? null;
        
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Customer ID diperlukan']);
            exit;
        }

        $query = "SELECT r.*, v.name as vehicle_name, v.plate_number, p.name as package_name, 
                 s.id as service_id, s.status as service_status 
                 FROM reservations r 
                 LEFT JOIN vehicles v ON r.vehicle_id = v.id 
                 LEFT JOIN packages p ON r.package_id = p.id 
                 LEFT JOIN services s ON r.id = s.reservation_id 
                 WHERE r.customer_id = ?
                 ORDER BY r.reservation_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservations = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $reservations]);
        break;

    case 'POST':
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);
        
        if (!isset($data['customer_id']) || !isset($data['vehicle_id']) || 
            !isset($data['package_id']) || !isset($data['reservation_date']) || 
            !isset($data['reservation_time']) || !isset($data['vehicle_complaint'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            exit;
        }

        // Get package detail
        $query = "SELECT p.*, GROUP_CONCAT(pr.id, ':', pr.name, ':', pr.price) as products 
                 FROM packages p 
                 LEFT JOIN package_products pp ON p.id = pp.package_id 
                 LEFT JOIN products pr ON pp.product_id = pr.id 
                 WHERE p.id = ?
                 GROUP BY p.id";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $data['package_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $package = $result->fetch_assoc();

        // Insert reservation
        $query = "INSERT INTO reservations (customer_id, vehicle_id, package_id, package_detail, 
                 vehicle_complaint, reservation_date, reservation_time) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        $packageDetail = json_encode($package);
        $stmt->bind_param('iiissss',
            $data['customer_id'],
            $data['vehicle_id'],
            $data['package_id'],
            $packageDetail,
            $data['vehicle_complaint'],
            $data['reservation_date'],
            $data['reservation_time']
        );

        if ($stmt->execute()) {
            $reservationId = $conn->insert_id;

            // Create service record
            $query = "INSERT INTO services (reservation_id, service_date) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $serviceDate = $data['reservation_date'];
            $stmt->bind_param('is', $reservationId, $serviceDate);
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'Reservasi berhasil dibuat']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membuat reservasi']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['status'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            exit;
        }

        $query = "UPDATE reservations SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $data['status'], $data['id']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Status reservasi berhasil diperbarui']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status reservasi']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID reservasi diperlukan']);
            exit;
        }

        $query = "DELETE FROM reservations WHERE id = ? AND customer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $data['id'], $data['customer_id']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Reservasi berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus reservasi']);
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