<?php

include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT DATE_FORMAT(orders.created_at, '%M') AS name,  COUNT(DISTINCT orders.order_id) AS total
        FROM orders INNER JOIN order_status ON orders.order_id = order_status.order_id INNER JOIN order_products ON orders.order_id = order_products.order_id INNER JOIN product ON product.product_id = order_products.product_id WHERE (order_status.status = 'Delivered' AND product.product_name =  :product_name )
        GROUP BY MONTH(orders.created_at)";



        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $product_name = $_GET['product_name'];

            $stmt->bindParam(':product_name', $product_name);



            $stmt->execute();
            $orders_monthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($orders_monthly);
        }
        break;
}
