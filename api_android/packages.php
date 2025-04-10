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
    
    $query = "SELECT p.* FROM packages p WHERE p.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $package = $result->fetch_assoc();
        
        // Get products in package
        $query = "SELECT pr.* FROM products pr 
                 JOIN package_products pp ON pr.id = pp.product_id 
                 WHERE pp.package_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $packageId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $package['products'] = $products;
        
        echo json_encode(['status' => 'success', 'data' => $package]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Paket tidak ditemukan']);
    }
} else {
    // Get all packages
    $query = "SELECT * FROM packages";
    $result = $conn->query($query);
    
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $packages]);
}

$conn->close();
?> 