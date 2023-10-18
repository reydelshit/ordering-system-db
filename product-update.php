<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        // Ensure that the product_id is provided in the GET request
        if (!isset($_GET['product_id'])) {
            $response = [
                "status" => "error",
                "message" => "Product ID not provided in the request."
            ];
        } else {
            // Retrieve product information
            $product_id = $_GET['product_id'];
            $sql = "SELECT * FROM product WHERE product_id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $sqlImages = "SELECT image_id, images_data FROM product_images WHERE product_id = :product_id";
                $stmtImages = $conn->prepare($sqlImages);
                $stmtImages->bindParam(':product_id', $product_id);
                $stmtImages->execute();
                $images = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

                // Combine product and image data
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


        $lastProductId = $conn->lastInsertId(); // Get the last inserted product_id

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

    case "PUT":


        $product = json_decode(file_get_contents('php://input'));
        $indicator = $product->indicator;

        if ($product->indicator === 'update_workout') {
            $sql = "UPDATE product SET product_name=:product_name, workout_description=:workout_description, workout_mins=:workout_mins, updated_at=:updated_at WHERE workout_id = :workout_id";
            $stmt = $conn->prepare($sql);
            $updated_at = date('Y-m-d');
            $stmt->bindParam(':workout_id', $product->id);
            $stmt->bindParam(':product_name', $product->product_name);
            $stmt->bindParam(':workout_description', $product->workout_description);
            $stmt->bindParam(':workout_mins', $product->workout_mins);
            $stmt->bindParam(':updated_at', $updated_at);

            if ($stmt->execute()) {
                $response = [
                    "status" => "success",
                    "message" => "User updated successfully"
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "User update failed"
                ];
            }
        }


        if ($indicator === 'update_workout_status') {
            $sql = "UPDATE product SET workout_status = :workout_status WHERE workout_id = :workout_id";
            $stmt2 = $conn->prepare($sql); // Use a different variable for the second query's prepared statement
            $stmt2->bindParam(':workout_status', $product->workout_status);
            $stmt2->bindParam(':workout_id', $product->workout_id);

            if ($stmt2->execute()) {
                $response = [
                    "status" => "success",
                    "message" => "Workout status update successfully"
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Workout status update failed"
                ];
            }
            echo json_encode($response);
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
