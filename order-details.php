<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $sql = "SELECT * FROM order_details WHERE order_details.order_id = :order_id";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);


            if (isset($order_id)) {
                $stmt->bindParam(':order_id', $order_id);
            }

            $stmt->execute();
            $order = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($order);
        }


        break;
}
