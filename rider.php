<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $sql = "SELECT * FROM assigned_riders WHERE order_id = :order_id ";
        }

        if (isset($_GET['rider_id'])) {
            $rider_id = $_GET['rider_id'];
            $sql = "SELECT * FROM assigned_riders WHERE rider_id = :rider_id ";
        }


        if (!isset($_GET['order_id'])) {
            $sql = "SELECT * FROM users WHERE user_type = 'rider'";
        }



        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($order_id)) {
                $stmt->bindParam(':order_id', $order_id);
            }

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
        $sql = "INSERT INTO assigned_riders (rider_id, order_id, date, rider_name, customer_name, status) VALUES (:rider_id, :order_id, :date, :rider_name, :customer_name, :status)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d H:i:s');
        $status = "Pending";
        $stmt->bindParam(':rider_id', $rider->rider_id);
        $stmt->bindParam(':order_id', $rider->order_id);
        $stmt->bindParam(':date', $created_at);
        $stmt->bindParam(':rider_name', $rider->rider_name);
        $stmt->bindParam(':customer_name', $rider->customer_name);
        $stmt->bindParam(':status', $status);




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


    case "PUT":
        $rider = json_decode(file_get_contents('php://input'));

        $sql = "UPDATE assigned_riders SET status=:status WHERE order_id = :order_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':order_id', $rider->order_id);
        $stmt->bindParam(':status', $rider->status);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "assigned_riders updated successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "assigned_riders update failed"
            ];
        }

        echo json_encode($response);

        break;
}
