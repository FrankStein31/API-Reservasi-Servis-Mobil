<?php
require_once 'config/connection.php';
require_once '../vendor/autoload.php';

// Set header untuk response JSON
header('Content-Type: application/json');

// Konfigurasi Midtrans
$serverKey = 'SB-Mid-server-mZSxOOkTxAfP_KsMx1fSOHA4';
\Midtrans\Config::$serverKey = $serverKey;
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['service_id'])) {
            // Get detail pembayaran berdasarkan service_id
            $serviceId = $_GET['service_id'];
            
            $query = "SELECT p.*, s.reservation_id, r.vehicle_id, v.name as vehicle_name, v.plate_number 
                     FROM payments p
                     JOIN services s ON p.service_id = s.id
                     JOIN reservations r ON s.reservation_id = r.id
                     JOIN vehicles v ON r.vehicle_id = v.id
                     WHERE p.service_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $serviceId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $payment = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $payment]);
            } else {
                // Jika tidak ada pembayaran, langsung ambil data service dan hitung bill
                $query = "SELECT s.*, r.vehicle_id, r.package_id, r.vehicle_complaint, r.package_detail,
                         v.name as vehicle_name, v.plate_number, 
                         p.name as package_name, 
                         c.name as customer_name, c.email, c.phone
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
                    
                    // Hitung bill dari package_detail
                    $bill = 0;
                    if (!empty($service['package_detail'])) {
                        try {
                            $packageDetail = json_decode($service['package_detail'], true);
                            
                            // Jika ada price langsung di packageDetail
                            if (isset($packageDetail['price'])) {
                                $bill = floatval($packageDetail['price']);
                            }
                            // Jika ada produk dalam packageDetail (format baru)
                            else if (isset($packageDetail['products']) && is_array($packageDetail['products'])) {
                                foreach ($packageDetail['products'] as $product) {
                                    if (isset($product['price'])) {
                                        $bill += floatval($product['price']);
                                    }
                                }
                            }
                            // Jika ada produk dalam format lama (string dengan format id:name:price)
                            else if (isset($packageDetail['products']) && is_string($packageDetail['products'])) {
                                $productsStr = $packageDetail['products'];
                                $productPairs = explode(',', $productsStr);
                                
                                foreach ($productPairs as $pair) {
                                    $parts = explode(':', $pair);
                                    if (count($parts) >= 3) {
                                        $bill += floatval($parts[2]);
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            // Log error atau biarkan bill tetap 0
                        }
                    }
                    
                    // Default biaya servis 
                    if ($bill == 0) {
                        $bill = 50000;
                    }
                    
                    $service['bill'] = $bill;
                    
                    // Jika service sudah selesai, izinkan pembayaran
                    if ($service['status'] == 'Finish') {
                        echo json_encode([
                            'status' => 'ready', 
                            'message' => 'Data pembayaran siap',
                            'data' => $service
                        ]);
                    } else {
                        // Jika belum selesai, tidak bisa bayar
                        echo json_encode([
                            'status' => 'pending', 
                            'message' => 'Servis belum selesai, belum bisa melakukan pembayaran',
                            'data' => $service
                        ]);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Service tidak ditemukan']);
                }
            }
        } else if (isset($_GET['customer_id'])) {
            // Get semua pembayaran milik customer
            $customerId = $_GET['customer_id'];
            
            $query = "SELECT p.*, s.reservation_id, r.vehicle_id, v.name as vehicle_name, v.plate_number,
                     r.package_id, pk.name as package_name, s.service_date
                     FROM payments p
                     JOIN services s ON p.service_id = s.id
                     JOIN reservations r ON s.reservation_id = r.id
                     JOIN vehicles v ON r.vehicle_id = v.id
                     JOIN packages pk ON r.package_id = pk.id
                     WHERE v.customer_id = ?
                     ORDER BY p.id DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $payments]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['service_id']) || !isset($data['bill'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            exit;
        }
        
        // Dapatkan service detail untuk transaksi
        $serviceId = $data['service_id'];
        $bill = $data['bill'];
        
        $query = "SELECT s.*, r.vehicle_id, r.package_id, r.vehicle_complaint,
                 v.name as vehicle_name, v.plate_number, v.customer_id,
                 p.name as package_name,
                 c.name as customer_name, c.email, c.phone
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
            
            // Cek apakah sudah pernah bayar
            $checkQuery = "SELECT id FROM payments WHERE service_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('i', $serviceId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Pembayaran sudah dilakukan sebelumnya']);
                exit;
            }
            
            // Buat transaksi Midtrans
            $orderId = 'SERVICE-' . $serviceId . '-' . time();
            
            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => (int)$bill
            ];
            
            $customerDetails = [
                'first_name' => $service['customer_name'],
                'email' => $service['email'],
                'phone' => $service['phone']
            ];
            
            $itemDetails = [
                [
                    'id' => 'SRV' . $serviceId,
                    'price' => (int)$bill,
                    'quantity' => 1,
                    'name' => $service['package_name'] . ' - ' . $service['vehicle_name']
                ]
            ];
            
            $transactionData = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails
            ];
            
            try {
                // Buat Snap Token
                $snapToken = \Midtrans\Snap::getSnapToken($transactionData);
                
                // Insert ke tabel payments
                $method = $data['method'] ?? 'Midtrans';
                $pay = $bill;
                $change = 0;
                $note = "Pembayaran via " . $method;
                
                $query = "INSERT INTO payments (service_id, bill, method, pay, `change`, note, snap_token, order_id) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('iddiisss', 
                    $serviceId,
                    $bill,
                    $pay,
                    $change,
                    $note,
                    $snapToken,
                    $orderId
                );
                
                if ($stmt->execute()) {
                    $paymentId = $conn->insert_id;
                    
                    // Update status service menjadi Paid
                    $updateQuery = "UPDATE services SET status = 'Paid' WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param('i', $serviceId);
                    $updateStmt->execute();
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Pembayaran berhasil dibuat',
                        'data' => [
                            'id' => $paymentId,
                            'snap_token' => $snapToken,
                            'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan pembayaran: ' . $stmt->error
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error Midtrans: ' . $e->getMessage()
                ]);
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