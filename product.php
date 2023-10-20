<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['user_id'])) {
            $user_id_specific_user = $_GET['user_id'];
            $sql = "SELECT * FROM water WHERE user_id = :user_id";
        }

        if (isset($_GET['product_id'])) {
            $product_specific_user = $_GET['product_id'];
            $sql = "SELECT * FROM product WHERE product_id = :product_id";
        }

        if (!isset($_GET['user_id']) && !isset($_GET['product_id'])) {
            $sql = "SELECT * FROM product ORDER BY product_id DESC";
        }


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($user_id_specific_user)) {
                $stmt->bindParam(':user_id', $user_id_specific_user);
            }

            if (isset($product_specific_user)) {
                $stmt->bindParam(':product_id', $product_specific_user);
            }

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


        break;

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

    case "PUT":

        $product = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE product
                SET product_name = :product_name, 
                    product_price = :product_price, 
                    quantity = :quantity, 
                    product_image = :product_image, 
                    product_description = :product_description, 
                    tags = :tags, 
                    product_category = :product_category
                WHERE product_id = :product_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':product_id', $product->product_id);
        $stmt->bindParam(':product_name', $product->product_name);
        $stmt->bindParam(':product_price', $product->product_price);
        $stmt->bindParam(':quantity', $product->quantity);
        $stmt->bindParam(':product_image', $product->product_image);
        $stmt->bindParam(':product_description', $product->product_description);
        $stmt->bindParam(':tags', $product->tags);
        $stmt->bindParam(':product_category', $product->product_category);

        $productUpdated = $stmt->execute();

        foreach ($product->images_data as $image_data) {
            // Insert the image into the 'product_images' table
            $sqlImages = "INSERT INTO product_images (product_id, images_data) VALUES (:product_id, :images_data)";
            $stmtImages = $conn->prepare($sqlImages);

            $stmtImages->bindParam(':product_id', $product->product_id);
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

        break;

    case "DELETE":
        $sql = "DELETE FROM product WHERE product_id = :product_id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $path[3]);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "product deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "product deletion failed"
            ];
        }
}
