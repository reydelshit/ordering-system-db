<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT status, COUNT(*) AS count FROM order_status GROUP BY status";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $pie = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($pie);
        }


        break;
}
