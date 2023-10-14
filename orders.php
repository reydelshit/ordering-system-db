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
        $orders = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO order (order_id, user_id, order_date, total_amount, payment_type) VALUES (NULL, user_id, order_date, total_amount, payment_type)";
        $stmt = $conn->prepare($sql);
        $order_date = date('Y-m-d');
        $stmt->bindParam(':user_id', $orders->user_id);
        $stmt->bindParam(':product_price', $orders->product_price);
        $stmt->bindParam(':order_date', $orders->order_date);
        $stmt->bindParam(':total_amount',  $orders->total_amount);
        $stmt->bindParam(':payment_type',  $orders->payment_type);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User added order successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User order failed"
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
