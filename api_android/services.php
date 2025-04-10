<?php
require_once 'config/connection.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $serviceId = $_GET['id'];
        
        $query = "SELECT s.*, r.vehicle_id, r.package_id, r.vehicle_complaint, r.package_detail,
                 v.name as vehicle_name, v.plate_number, v.brand, v.year, 
                 p.name as package_name,
                 c.name as customer_name, c.email, c.phone, c.address
                 FROM services s
                 JOIN reservations r ON s.reservation_id = r.id
                 JOIN vehicles v ON r.vehicle_id = v.id
                 JOIN packages p ON r.package_id = p.id
                 JOIN customers c ON v.customer_id = c.id
                 WHERE s.id = ?";
                 
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $service = $result->fetch_assoc();
            
            // Ambil harga dari package_detail yang tersimpan dalam JSON
            if (!empty($service['package_detail'])) {
                $packageDetail = json_decode($service['package_detail'], true);
                if (isset($packageDetail['price'])) {
                    $service['bill'] = $packageDetail['price'];
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $service
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Servis tidak ditemukan'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID servis diperlukan'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
}

$conn->close(); 