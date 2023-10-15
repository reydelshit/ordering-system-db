<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['user_id'])) {
            $user_id_specific_user = $_GET['user_id'];
            $sql = "SELECT * FROM users WHERE user_id = :user_id";
        }

        if (isset($_GET['product_id'])) {
            $product_specific_user = $_GET['product_id'];
            $sql = "SELECT feedback.feedback_id, feedback.feedback_description, feedback.feedback_rating, feedback.feedback_date, users.name, users.email, users.profile_picture FROM feedback LEFT JOIN users ON feedback.user_id = users.user_id  WHERE product_id = :product_id";
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
        $feedback = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO feedback (feedback_description, feedback_rating, feedback_date, product_id, user_id, created_at) 
                VALUES (:feedback_description, :feedback_rating, :feedback_date, :product_id, :user_id, :created_at)";
        $stmt = $conn->prepare($sql);
        $feedback_date = date('Y-m-d');
        $created_at = date('Y-m-d');
        $stmt->bindParam(':feedback_description', $feedback->feedback_description);
        $stmt->bindParam(':feedback_rating', $feedback->feedback_rating);
        $stmt->bindParam(':feedback_date', $feedback_date);
        $stmt->bindParam(':product_id', $feedback->product_id);
        $stmt->bindParam(':user_id', $feedback->user_id);
        $stmt->bindParam(':created_at', $created_at);


        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User created feedback successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User creation feedback failed"
            ];
        }

        echo json_encode($response);
        break;

    case "PUT":
        $user = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE users SET name= :name, email=:email, gender=:gender, profile_picture=:profile_picture, address=:address, profile_description=:profile_description, updated_at=:updated_at WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':user_id', $user->user_id);
        $stmt->bindParam(':name', $user->name);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':profile_picture', $user->profile_picture);
        $stmt->bindParam(':address', $user->address);
        $stmt->bindParam(':gender', $user->gender);
        $stmt->bindParam(':profile_description', $user->profile_description);
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

        echo json_encode($response);
        break;

    case "DELETE":
        $sql = "DELETE FROM users WHERE id = :id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $path[2]);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User deletion failed"
            ];
        }
}
