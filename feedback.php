<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['user_id'])) {
            $user_id_specific_user = $_GET['user_id'];
            $sql = "SELECT * FROM feedback WHERE user_id = :user_id";
        }

        if (isset($_GET['product_id'])) {
            $product_specific_user = $_GET['product_id'];
            $sql = "SELECT feedback.feedback_id, feedback.feedback_description, feedback.feedback_rating, feedback.feedback_date, users.user_id, users.name, users.email, users.profile_picture FROM feedback LEFT JOIN users ON feedback.user_id = users.user_id  WHERE product_id = :product_id";
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
}
