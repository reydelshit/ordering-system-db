<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (!isset($_GET['product_id'])) {
            $response = [
                "status" => "error",
                "message" => "Product ID not provided in the request."
            ];
        } else {

            $product_id = $_GET['product_id'];
            $sql = "SELECT * FROM product WHERE product_id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $sqlImages = "SELECT image_id, images_data, product_id FROM product_images WHERE product_id = :product_id";
                $stmtImages = $conn->prepare($sqlImages);
                $stmtImages->bindParam(':product_id', $product_id);
                $stmtImages->execute();
                $images = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

                $product["images_data"] = $images;

                $response = [
                    "status" => "success",
                    "data" => $product
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Product not found."
                ];
            }
        }
        echo json_encode($response);
        break;
}
