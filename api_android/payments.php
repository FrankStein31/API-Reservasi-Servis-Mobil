<?php
require_once 'config/connection.php';
require_once '../vendor/autoload.php';

// Set header untuk response JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim notifikasi WhatsApp menggunakan API Fonnte
function sendWhatsAppNotification($conn, $customerId, $message) {
    // Ambil nomor telepon pelanggan
    $query = "SELECT phone FROM customers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $customer = $result->fetch_assoc();
    $phoneNumber = $customer['phone'];
    
    // Pastikan nomor telepon valid
    if (empty($phoneNumber)) {
        return false;
    }
    
    // Token API Fonnte
    $token = "UPvy5unaoPHJggKLHW6V"; // Token API Fonnte
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $phoneNumber,
            'message' => $message,
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: $token"
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    
    return $response;
}

// Konfigurasi Midtrans
$serverKey = 'SB-Mid-server-MNo3xTYokgclNykKFrjUtVDg';
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
                    if ($service['status'] == 'Selesai') {
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
            
            // Buat parameter untuk Snap API
            $params = array(
                'transaction_details' => array(
                    'order_id' => $orderId,
                    'gross_amount' => (int)$bill,
                ),
                'customer_details' => array(
                    'first_name' => $service['customer_name'],
                    'email' => $service['email'],
                    'phone' => $service['phone'],
                ),
                'item_details' => array(
                    array(
                        'id' => 'SERVICE-' . $serviceId,
                        'price' => (int)$bill,
                        'quantity' => 1,
                        'name' => 'Pembayaran Service ID #' . $serviceId,
                    ),
                ),
            );
            
            try {
                // Get Snap Payment Page URL
                $snapToken = \Midtrans\Snap::getSnapToken($params);
                error_log("Snap Token: " . $snapToken);
                
                // Insert ke tabel payments
                $methodValue = 'Card'; // Gunakan 'Card' untuk pembayaran Midtrans karena enum hanya boleh 'Cash' atau 'Card'
                $pay = $bill;
                $change = 0;
                $note = "Pembayaran via " . ($data['method'] ?? 'Midtrans') . " | Order ID: " . $orderId;
                
                $query = "INSERT INTO payments (service_id, bill, method, pay, `change`, note, snap_token) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('idsdiss', 
                    $serviceId,
                    $bill,
                    $methodValue,
                    $pay,
                    $change,
                    $note,
                    $snapToken
                );
                
                if ($stmt->execute()) {
                    $paymentId = $conn->insert_id;
                    
                    // Update status service menjadi Finish karena enum hanya menerima 'Pending', 'Process', 'Finish'
                    $updateQuery = "UPDATE services SET status = 'Finish' WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param('i', $serviceId);
                    $updateStmt->execute();
                    
                    // Kirim notifikasi WhatsApp untuk pembayaran berhasil
                    $formattedBill = number_format($bill, 0, ',', '.');
                    $paymentMethod = $data['method'] ?? 'Midtrans';
                    
                    // Pesan notifikasi WhatsApp
                    $message = "Halo {$service['customer_name']}!\n\n";
                    $message .= "Pembayaran servis kendaraan *{$service['vehicle_name']}* berhasil dibuat.\n\n";
                    $message .= "Detail Pembayaran:\n";
                    $message .= "- ID Pembayaran: #$paymentId\n";
                    $message .= "- Kendaraan: {$service['vehicle_name']} ({$service['plate_number']})\n";
                    $message .= "- Paket: {$service['package_name']}\n";
                    $message .= "- Total: Rp $formattedBill\n";
                    $message .= "- Metode: $paymentMethod\n\n";
                    $message .= "Terima kasih telah melakukan pembayaran. Anda dapat mengakses bukti pembayaran melalui aplikasi.";
                    
                    // Kirim notifikasi WhatsApp
                    sendWhatsAppNotification($conn, $service['customer_id'], $message);
                    
                    // Respons jika berhasil membuat pembayaran
                    $response = [
                        'status' => 'success',
                        'message' => 'Pembayaran berhasil dibuat',
                        'snap_token' => $snapToken,
                        'order_id' => $orderId
                    ];
                    echo json_encode($response);
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