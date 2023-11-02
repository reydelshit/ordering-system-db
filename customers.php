<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT users.user_id, users.name, users.email, users.address, COUNT(orders.user_id) AS orders_count, COUNT(DISTINCT feedback.feedback_id) AS feedback_count
        FROM users INNER JOIN orders ON orders.user_id = users.user_id
        LEFT JOIN feedback ON feedback.user_id = users.user_id
        WHERE users.user_type = 'user'
        GROUP BY users.user_id";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($customers);
        }


        break;
}
