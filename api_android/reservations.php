<?php
require_once 'config/connection.php';

// Set header untuk response JSON
header('Content-Type: application/json');

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get reservasi detail by ID
            $reservationId = $_GET['id'];
            
            $query = "SELECT r.*, v.name as vehicle_name, v.plate_number, p.name as package_name,
                     s.id as service_id, s.status as service_status, s.service_date 
                     FROM reservations r
                     JOIN vehicles v ON r.vehicle_id = v.id
                     JOIN packages p ON r.package_id = p.id
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
        } else if (isset($_GET['customer_id'])) {
            // Get reservasi by customer ID
            $customerId = $_GET['customer_id'];
            
            $query = "SELECT r.*, v.name as vehicle_name, v.plate_number, p.name as package_name,
                     s.id as service_id, s.status as service_status 
                     FROM reservations r
                     JOIN vehicles v ON r.vehicle_id = v.id
                     JOIN packages p ON r.package_id = p.id
                     LEFT JOIN services s ON r.id = s.reservation_id
                     WHERE v.customer_id = ?
                     ORDER BY r.reservation_date DESC, r.reservation_time DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reservations = [];
            while ($row = $result->fetch_assoc()) {
                $reservations[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $reservations]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID atau customer_id diperlukan']);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Pastikan semua field wajib ada
        if (!isset($data['vehicle_id']) || !isset($data['package_id']) || 
            !isset($data['reservation_date']) || !isset($data['reservation_time'])) {
            echo json_encode(['status' => 'error', 'message' => 'Semua field diperlukan']);
            exit;
        }
        
        // Validasi kendaraan
        $vehicleQuery = "SELECT * FROM vehicles WHERE id = ?";
        $vehicleStmt = $conn->prepare($vehicleQuery);
        $vehicleStmt->bind_param('i', $data['vehicle_id']);
        $vehicleStmt->execute();
        $vehicleResult = $vehicleStmt->get_result();
        
        if ($vehicleResult->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Kendaraan tidak ditemukan']);
            exit;
        }
        
        $vehicle = $vehicleResult->fetch_assoc();
        $customerId = $vehicle['customer_id'];
        
        // Pastikan vehicle_complaint tidak null
        $vehicleComplaint = isset($data['vehicle_complaint']) ? $data['vehicle_complaint'] : '';
        
        // Ambil detail paket untuk disimpan
        $packageQuery = "SELECT * FROM packages WHERE id = ?";
        $packageStmt = $conn->prepare($packageQuery);
        $packageStmt->bind_param('i', $data['package_id']);
        $packageStmt->execute();
        $packageResult = $packageStmt->get_result();
        $packageData = $packageResult->fetch_assoc();
        $packageDetail = json_encode($packageData);
        
        // Insert reservasi baru
        $query = "INSERT INTO reservations (customer_id, vehicle_id, package_id, package_detail, vehicle_complaint, reservation_date, reservation_time, reservation_origin) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'Online')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiissss', 
            $customerId,
            $data['vehicle_id'],
            $data['package_id'],
            $packageDetail,
            $vehicleComplaint,
            $data['reservation_date'],
            $data['reservation_time']
        );

        if ($stmt->execute()) {
            $reservationId = $conn->insert_id;
            
            // Buat service baru dengan status Pending
            $serviceQuery = "INSERT INTO services (reservation_id, status, created_at, updated_at) 
                            VALUES (?, 'Pending', NOW(), NOW())";
            $serviceStmt = $conn->prepare($serviceQuery);
            $serviceStmt->bind_param('i', $reservationId);
            $serviceStmt->execute();
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Reservasi berhasil dibuat',
                'data' => ['id' => $reservationId]
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Gagal membuat reservasi: ' . $stmt->error
            ]);
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID reservasi diperlukan']);
            exit;
        }
        
        // Cek apakah service status masih Pending
        $query = "SELECT s.status FROM services s WHERE s.reservation_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $data['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $service = $result->fetch_assoc();
            
            if ($service['status'] !== 'Pending') {
                echo json_encode(['status' => 'error', 'message' => 'Reservasi tidak dapat dibatalkan karena sedang diproses']);
                exit;
            }
            
            // Hapus service terkait dulu
            $query = "DELETE FROM services WHERE reservation_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $data['id']);
            $stmt->execute();
            
            // Baru hapus reservasi
            $query = "DELETE FROM reservations WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Reservasi berhasil dibatalkan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal membatalkan reservasi']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Service tidak ditemukan']);
        }
        break;
        
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Method tidak diizinkan'
        ]);
        break;
}

$conn->close();
?> 