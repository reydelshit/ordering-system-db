<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['product_id'])) {
            $product_specific_user = $_GET['product_id'];
            $sql = "SELECT product.product_name, feedback.feedback_description, feedback.created_at, feedback.feedback_rating, 
            users.name, users.profile_picture, users.user_id, feedback.feedback_id
            FROM product
            LEFT JOIN feedback ON product.product_id = feedback.product_id
            LEFT JOIN users ON feedback.user_id = users.user_id
            WHERE product.product_id = :product_id";
        }

        if (!isset($_GET['product_id'])) {
            $sql = "SELECT product.product_image, product.product_id, feedback.feedback_id, product.product_name, COUNT(feedback.feedback_id) AS total_feedback
            FROM product
            LEFT JOIN feedback ON product.product_id = feedback.product_id
            GROUP BY product.product_name";
        }


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($product_specific_user)) {
                $stmt->bindParam(':product_id', $product_specific_user);
            }

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


        break;
}
