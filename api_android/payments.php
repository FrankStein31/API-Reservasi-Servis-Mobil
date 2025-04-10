<?php
require_once 'config/connection.php';
require_once '../vendor/autoload.php';

// Set your Merchant Server Key
\Midtrans\Config::$serverKey = 'SB-Mid-server-61XuGAwQ8Bj8LxSS3GzE';
// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
\Midtrans\Config::$isProduction = false;
// Set sanitization on (default)
\Midtrans\Config::$isSanitized = true;
// Set 3DS transaction for credit card to true
\Midtrans\Config::$is3ds = true;

// Set header untuk response JSON
header('Content-Type: application/json');

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['service_id'])) {
            // Get detail pembayaran berdasarkan service_id
            $serviceId = $_GET['service_id'];
            
            $query = "SELECT p.*, a.name as admin_name FROM payments p 
                     LEFT JOIN admins a ON p.admin_id = a.id 
                     WHERE p.service_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $serviceId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $payment = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $payment]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pembayaran belum dilakukan']);
            }
        } elseif (isset($_GET['customer_id'])) {
            // Get semua pembayaran milik customer tertentu
            $customerId = $_GET['customer_id'];
            
            $query = "SELECT p.*, s.service_date, s.reservation_id, r.vehicle_id, r.package_id, 
                     v.name as vehicle_name, v.plate_number, pa.name as package_name 
                     FROM payments p 
                     JOIN services s ON p.service_id = s.id 
                     JOIN reservations r ON s.reservation_id = r.id 
                     JOIN vehicles v ON r.vehicle_id = v.id 
                     JOIN packages pa ON r.package_id = pa.id 
                     WHERE r.customer_id = ? 
                     ORDER BY p.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $payments]);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['service_id']) || !isset($data['bill'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            exit;
        }
        
        $serviceId = $data['service_id'];
        $bill = $data['bill'];
        
        // Get service detail
        $query = "SELECT s.*, v.name as vehicle_name, v.plate_number, c.name as customer_name, 
                 c.email, c.phone 
                 FROM services s
                 JOIN vehicles v ON s.vehicle_id = v.id
                 JOIN customers c ON v.customer_id = c.id
                 WHERE s.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Servis tidak ditemukan']);
            exit;
        }
        
        $service = $result->fetch_assoc();
        
        // Create Midtrans transaction
        $transaction_details = array(
            'order_id' => 'ORDER-' . time(),
            'gross_amount' => $bill
        );
        
        $customer_details = array(
            'first_name' => $service['customer_name'],
            'email' => $service['email'],
            'phone' => $service['phone']
        );
        
        $item_details = array(
            array(
                'id' => $serviceId,
                'price' => $bill,
                'quantity' => 1,
                'name' => 'Servis Mobil - ' . $service['vehicle_name'] . ' (' . $service['plate_number'] . ')'
            )
        );
        
        $transaction = array(
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details
        );
        
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($transaction);
            
            // Simpan data pembayaran
            $query = "INSERT INTO payments (service_id, bill, snap_token, status) VALUES (?, ?, ?, 'pending')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ids', $serviceId, $bill, $snapToken);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $snapToken
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data pembayaran'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
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