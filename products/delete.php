<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

$stmt = $conn->prepare("DELETE FROM products WHERE id=?");
try {
    $stmt->execute([$data['id']]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            "status" => 404,
            "message" => "Product not found"
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Product deleted successfully",
        "data" => null
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 500,
        "message" => "Failed to delete product"
    ]);
}
