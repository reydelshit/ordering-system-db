<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['user_id'])) {
            $user_id_specific_user = $_GET['user_id'];
            $sql = "SELECT product.product_price, product.product_image, product.product_name, cart.qty FROM 
            ((cart LEFT JOIN product ON cart.product_id = product.product_id) 
            LEFT JOIN users ON cart.user_id = users.user_id) WHERE cart.user_id = :user_id";
        }

        // if (isset($_GET['product_id'])) {
        //     $water_specific_user = $_GET['water_id'];
        //     $sql = "SELECT * FROM water WHERE water_id = :water_id";
        // }


        if (!isset($_GET['user_id']) && !isset($_GET['product_id'])) {
            $sql = "SELECT * FROM product ORDER BY product_id DESC";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($user_id_specific_user)) {
                $stmt->bindParam(':user_id', $user_id_specific_user);
            }

            if (isset($medical_specific_user)) {
                $stmt->bindParam(':water_id', $water_specific_user);
            }

            $stmt->execute();
            $sleep = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($sleep);
        }
        break;

    case "POST":
        $product = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO cart (cart_id, product_id, user_id, qty) VALUES (NULL,  :product_id, :user_id, :qty)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':product_id', $product->product_id);
        $stmt->bindParam(':user_id', $product->user_id);
        $stmt->bindParam(':qty', $product->qty);


        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Admin added product successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Admin added product failed"
            ];
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
