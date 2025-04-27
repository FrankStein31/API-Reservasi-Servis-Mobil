<?php
require_once 'config/connection.php';

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

// Function to get package details including products and price
function getPackageDetails($conn, $packageId) {
    // Get package info
    $query = "SELECT * FROM packages WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $package = $result->fetch_assoc();
    
    // Get products in package
    $query = "SELECT p.id, p.name, p.price FROM products p 
              JOIN package_products pp ON p.id = pp.product_id 
              WHERE pp.package_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    $totalPrice = 0;
    
    while ($row = $result->fetch_assoc()) {
        $productPrice = (float)$row['price'];
        $products[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'price' => $productPrice
        ];
        $totalPrice += $productPrice;
    }
    
    return [
        'id' => (int)$package['id'],
        'name' => $package['name'],
        'description' => isset($package['description']) ? $package['description'] : null,
        'products' => $products,
        'price' => $totalPrice
    ];
}

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get reservasi detail by ID
            $reservationId = $_GET['id'];
            
            $query = "SELECT r.*, v.name as vehicle_name, v.plate_number, p.name as package_name,
                     s.id as service_id, s.status as service_status, s.service_date,
                     IFNULL((SELECT COUNT(*) FROM payments WHERE service_id = s.id), 0) as payment_exists 
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
                     s.id as service_id, s.status as service_status,
                     IFNULL((SELECT COUNT(*) FROM payments WHERE service_id = s.id), 0) as payment_exists 
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
        
        // Validasi data
        if (!isset($data['customer_id']) || !isset($data['vehicle_id']) || !isset($data['package_id']) || 
            !isset($data['reservation_date']) || !isset($data['reservation_time']) || !isset($data['vehicle_complaint'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            exit;
        }
        
        // Get package details
        $packageDetails = getPackageDetails($conn, $data['package_id']);
        if (!$packageDetails) {
            echo json_encode(['status' => 'error', 'message' => 'Paket tidak ditemukan']);
            exit;
        }
        
        // Save package details as JSON for later reference
        $packageDetailJson = json_encode($packageDetails);
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert into reservations table
            $query = "INSERT INTO reservations (customer_id, vehicle_id, package_id, package_detail, reservation_date, reservation_time, vehicle_complaint) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('iiissss', 
                $data['customer_id'], 
                $data['vehicle_id'], 
                $data['package_id'],
                $packageDetailJson,
                $data['reservation_date'], 
                $data['reservation_time'], 
                $data['vehicle_complaint']
            );
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $reservationId = $conn->insert_id;
                
                // Insert into services table
                $query = "INSERT INTO services (reservation_id, status) VALUES (?, 'Pending')";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $reservationId);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $conn->commit();
                    
                    // Dapatkan informasi kendaraan untuk notifikasi
                    $query = "SELECT v.name as vehicle_name FROM vehicles v WHERE v.id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $data['vehicle_id']);
                    $stmt->execute();
                    $vehicleResult = $stmt->get_result();
                    $vehicleData = $vehicleResult->fetch_assoc();
                    
                    // Pesan notifikasi WhatsApp
                    $message = "Halo! Reservasi servis kendaraan *{$vehicleData['vehicle_name']}* anda telah berhasil dibuat.\n\n";
                    $message .= "Detail Reservasi:\n";
                    $message .= "- Tanggal: {$data['reservation_date']}\n";
                    $message .= "- Waktu: {$data['reservation_time']}\n";
                    $message .= "- Paket: {$packageDetails['name']}\n";
                    $message .= "- Total Biaya: Rp ".number_format($packageDetails['price'], 0, ',', '.')." \n\n";
                    $message .= "Terima kasih telah menggunakan layanan kami.";
                    
                    // Kirim notifikasi WhatsApp
                    sendWhatsAppNotification($conn, $data['customer_id'], $message);
                    
                    echo json_encode(['status' => 'success', 'message' => 'Reservasi berhasil dibuat', 'data' => ['reservation_id' => $reservationId]]);
                } else {
                    $conn->rollback();
                    echo json_encode(['status' => 'error', 'message' => 'Gagal membuat layanan']);
                }
            } else {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Gagal membuat reservasi']);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID reservasi diperlukan']);
            exit;
        }
        
        // Check if service status is "Pending"
        $query = "SELECT s.status, r.customer_id, r.reservation_date, r.reservation_time, 
                 v.name as vehicle_name, p.name as package_name 
                 FROM services s 
                 JOIN reservations r ON s.reservation_id = r.id
                 JOIN vehicles v ON r.vehicle_id = v.id
                 JOIN packages p ON r.package_id = p.id
                 WHERE r.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $data['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['status'] !== 'Pending') {
                echo json_encode(['status' => 'error', 'message' => 'Tidak dapat membatalkan reservasi yang sudah diproses']);
                exit;
            }
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Delete the reservation and associated service
                $query = "DELETE FROM services WHERE reservation_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $data['id']);
                $stmt->execute();
                
                $query = "DELETE FROM reservations WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $data['id']);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $conn->commit();
                    
                    // Pesan notifikasi WhatsApp
                    $message = "Halo! Reservasi servis kendaraan *{$row['vehicle_name']}* anda telah berhasil dibatalkan.\n\n";
                    $message .= "Detail Reservasi yang Dibatalkan:\n";
                    $message .= "- Tanggal: {$row['reservation_date']}\n";
                    $message .= "- Waktu: {$row['reservation_time']}\n";
                    $message .= "- Paket: {$row['package_name']}\n\n";
                    $message .= "Terima kasih atas pengertian Anda.";
                    
                    // Kirim notifikasi WhatsApp
                    sendWhatsAppNotification($conn, $row['customer_id'], $message);
                    
                    echo json_encode(['status' => 'success', 'message' => 'Reservasi berhasil dibatalkan']);
                } else {
                    $conn->rollback();
                    echo json_encode(['status' => 'error', 'message' => 'Gagal membatalkan reservasi']);
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Reservasi tidak ditemukan']);
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