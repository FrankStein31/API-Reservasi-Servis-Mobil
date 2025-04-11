<?php
require_once 'config/connection.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Konfigurasi Midtrans
$serverKey = 'SB-Mid-server-MNo3xTYokgclNykKFrjUtVDg';
\Midtrans\Config::$serverKey = $serverKey;
\Midtrans\Config::$isProduction = false;

// Log semua request untuk debugging
$rawInput = file_get_contents('php://input');
$headers = getallheaders();
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Log untuk debugging
error_log("Midtrans Callback - Method: $method, URI: $uri");
error_log("Midtrans Callback - Headers: " . json_encode($headers));
error_log("Midtrans Callback - Raw Input: " . $rawInput);

// Handler untuk endpoint GET - yang dipanggil oleh Midtrans SDK saat memulai transaksi
if ($method === 'GET') {
    // Header harus json untuk response ke Midtrans SDK
    header('Content-Type: application/json');
    
    // API untuk mengambil snap token
    if (isset($_GET['order_id']) && isset($_GET['action']) && $_GET['action'] === 'token') {
        $orderId = $_GET['order_id'];
        
        $query = "SELECT snap_token FROM payments WHERE note LIKE ?";
        $param = "%Order ID: $orderId%";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $param);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $payment = $result->fetch_assoc();
            echo json_encode(['token' => $payment['snap_token']]);
        } else {
            echo json_encode(['error' => 'Token not found']);
        }
        exit;
    }
    
    // Default response untuk GET request lainnya
    echo json_encode(['status' => 'ok', 'message' => 'Midtrans callback endpoint']);
    exit;
}

// Handler untuk notification POST
if ($method === 'POST') {
    // Set header ke json untuk notification response
    header('Content-Type: application/json');
    
    try {
        $notification = json_decode($rawInput);
        
        // Validasi notifikasi dari Midtrans
        $status = \Midtrans\Transaction::status($notification->order_id);
        $orderId = $status->order_id;
        $transactionStatus = $status->transaction_status;
        $fraudStatus = isset($status->fraud_status) ? $status->fraud_status : null;
        
        // Extract service_id dari order_id (format: SERVICE-{service_id}-{timestamp})
        $orderParts = explode('-', $orderId);
        if (count($orderParts) >= 3 && $orderParts[0] === 'SERVICE') {
            $serviceId = $orderParts[1];
            
            // Update status pembayaran di database
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                // Transaksi berhasil
                $query = "UPDATE payments SET status = 'Paid' WHERE service_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $serviceId);
                $stmt->execute();
                
                error_log("Midtrans Callback - Payment success for service ID: $serviceId");
                echo json_encode(['status' => 'success', 'message' => 'Payment success']);
            } else if ($transactionStatus == 'pending') {
                // Transaksi pending
                error_log("Midtrans Callback - Payment pending for service ID: $serviceId");
                echo json_encode(['status' => 'pending', 'message' => 'Payment pending']);
            } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                // Transaksi gagal
                error_log("Midtrans Callback - Payment failed for service ID: $serviceId, status: $transactionStatus");
                echo json_encode(['status' => 'failed', 'message' => 'Payment failed']);
            }
        } else {
            error_log("Midtrans Callback - Invalid order ID format: $orderId");
            echo json_encode(['status' => 'error', 'message' => 'Invalid order ID format']);
        }
    } catch (Exception $e) {
        error_log('Midtrans Callback Error: ' . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Default response untuk method lain
header('Content-Type: application/json');
echo json_encode(['status' => 'error', 'message' => 'Invalid request method']); 