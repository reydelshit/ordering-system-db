<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {


    case "POST":
        $product = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO product (product_id, product_name, product_price, quantity, product_image, product_description, tags, product_category) VALUES (NULL, :product_name, :product_price, :quantity, :product_image, :product_description, :tags, :product_category)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':product_name', $product->product_name);
        $stmt->bindParam(':product_price', $product->product_price);
        $stmt->bindParam(':quantity', $product->quantity);
        $stmt->bindParam(':product_image',  $product->product_image);
        $stmt->bindParam(':product_description',  $product->product_description);
        $stmt->bindParam(':tags',  $product->tags);
        $stmt->bindParam(':product_category',  $product->product_category);


        $productInserted = $stmt->execute();


        $lastProductId = $conn->lastInsertId();

        foreach ($product->images_data as $image_data) {

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



    case "DELETE":
        $sql = "DELETE FROM product_images WHERE image_id = :image_id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':image_id', $path[3]);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "product image deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "product image deletion failed"
            ];
        }
}
