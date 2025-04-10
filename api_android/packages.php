<?php
require_once 'config/connection.php';

// Set header untuk response JSON
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// Get single package if ID is provided
if (isset($_GET['id'])) {
    $packageId = $_GET['id'];
    
    $query = "SELECT * FROM packages WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
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
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'price' => (float)$row['price']
            ];
        }
        
        // Hitung total harga dari produk
        $total_price = 0;
        foreach ($products as $product) {
            $total_price += $product['price'];
        }
        
        $response = [
            'status' => true,
            'message' => 'Data paket berhasil ditemukan',
            'data' => [
                'id' => (int)$package['id'],
                'name' => $package['name'],
                'description' => isset($package['description']) ? $package['description'] : null,
                'products' => $products,
                'price' => $total_price
            ]
        ];
    } else {
        $response = [
            'status' => false,
            'message' => 'Paket tidak ditemukan'
        ];
    }
    
    echo json_encode($response);
} else {
    // Get all packages
    $query = "SELECT * FROM packages";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $packages = [];
        
        while ($row = $result->fetch_assoc()) {
            // Query untuk mendapatkan produk dalam paket
            $product_query = "SELECT p.id, p.name, p.price FROM products p 
                             JOIN package_products pp ON p.id = pp.product_id 
                             WHERE pp.package_id = ?";
            $stmt = $conn->prepare($product_query);
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $product_result = $stmt->get_result();
            
            $products = [];
            $total_price = 0;
            
            while ($product = $product_result->fetch_assoc()) {
                $product_price = (float)$product['price'];
                $products[] = [
                    'id' => (int)$product['id'],
                    'name' => $product['name'],
                    'price' => $product_price
                ];
                $total_price += $product_price;
            }
            
            $packages[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'description' => isset($row['description']) ? $row['description'] : null,
                'products' => $products,
                'price' => $total_price
            ];
        }
        
        $response = [
            'status' => true,
            'message' => 'Data paket berhasil ditemukan',
            'data' => $packages
        ];
    } else {
        $response = [
            'status' => false,
            'message' => 'Tidak ada data paket'
        ];
    }
    
    echo json_encode($response);
}

$conn->close();
?> 