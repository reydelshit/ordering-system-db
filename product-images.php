<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        if (isset($_GET['product_id'])) {
            $product_specific_images = $_GET['product_id'];

            $sql2 = " SELECT * FROM product_images WHERE product_id = :product_id";


            $stmt = $conn->prepare($sql2);

            if (isset($product_specific_images)) {
                $stmt->bindParam(':product_id', $product_specific_images);
            }

            $stmt->execute();
            $product_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product_images);
        }



        break;

    case "POST":
        $product = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO product (product_id, product_name, product_price, quantity, product_image) VALUES (NULL, :product_name, :product_price, :quantity, :product_image)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':product_name', $product->product_name);
        $stmt->bindParam(':product_price', $product->product_price);
        $stmt->bindParam(':quantity', $product->quantity);
        $stmt->bindParam(':product_image',  $product->product_image);


        $productInserted = $stmt->execute();


        $lastProductId = $conn->lastInsertId();

        foreach ($product->images_data as $image_data) {
            // Insert the image into the 'product_images' table
            $sqlImages = "INSERT INTO product_images (product_id, images_data) VALUES (:product_id, :images_data)";
            $stmtImages = $conn->prepare($sqlImages);

            $stmtImages->bindParam(':product_id', $lastProductId);
            $stmtImages->bindParam(':images_data', $image_data);

            $imagesInserted = $stmtImages->execute();

            if ($imagesInserted) {
                $response = [
                    "status" => "success",
                    "message" => "Admin added product and images successfully"
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Admin added product successfully, but failed to add images"
                ];
            }
        }


        echo json_encode($response);
        break;
}
