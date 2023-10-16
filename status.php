<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case "PUT":
        $order_status = json_decode(file_get_contents('php://input'));

        $sql = "UPDATE order_status SET status=:status WHERE status_id=:status_id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':status', $order_status->status);
        $stmt->bindParam(':status_id', $order_status->status_id);


        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Admin updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Admin update failed"
            ];
        }

        break;
}
