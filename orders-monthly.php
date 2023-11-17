<?php

include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT DATE_FORMAT(orders.created_at, '%M') AS name, COUNT(DISTINCT orders.order_id) AS total
        FROM orders
        INNER JOIN order_status ON order_status.order_id = orders.order_id 
        WHERE order_status.status = 'Delivered'
        GROUP BY MONTH(orders.created_at)";

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);


            $stmt->execute();
            $orders_monthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($orders_monthly);
        }
        break;
}
