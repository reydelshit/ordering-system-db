<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['rider_id'])) {
            $rider_id = $_GET['rider_id'];
            $sql = "SELECT   assigned_riders.order_id, assigned_riders.status, assigned_riders.rider_id, assigned_riders.customer_name, assigned_riders.assigned_id, assigned_riders.date, order_details.phone, order_details.payment_type, order_details.delivery_address, GROUP_CONCAT(DISTINCT product.product_name) AS products FROM assigned_riders INNER JOIN order_details ON order_details.order_id = assigned_riders.order_id INNER JOIN order_products ON order_products.order_id = assigned_riders.order_id LEFT JOIN product ON product.product_id = order_products.product_id WHERE rider_id = :rider_id GROUP BY assigned_riders.order_id";
        }


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);


            if (isset($rider_id)) {
                $stmt->bindParam(':rider_id', $rider_id);
            }

            $stmt->execute();
            $rider = $stmt->fetchAll(PDO::FETCH_ASSOC);


            echo json_encode($rider);
        }


        break;

    case "POST":
        $rider = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO assigned_riders (rider_id, order_id, date, rider_name) VALUES (:rider_id, :order_id, :date, :rider_name)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d H:i:s');
        $stmt->bindParam(':rider_id', $rider->rider_id);
        $stmt->bindParam(':order_id', $rider->order_id);
        $stmt->bindParam(':date', $created_at);
        $stmt->bindParam(':rider_name', $rider->rider_name);


        if ($stmt->execute()) {

            $response = [
                "status" => "success",
                "message" => "assigned_riders successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "assigned_riders failed"
            ];
        }

        echo json_encode($response);
        break;
}
