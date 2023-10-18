<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":


        $sql = "SELECT comment_reply.feedback_id, comment_reply.reply_to, comment_reply.content, 
                comment_reply.created_at, users.name, users.profile_picture, users.user_type 
                FROM comment_reply INNER JOIN users ON comment_reply.user_id = users.user_id";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($product);
        }


        break;

    case "POST":
        $reply = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO comment_reply (content, reply_to, user_id, feedback_id, created_at) 
        VALUES (:content, :reply_to, :user_id, :feedback_id, :created_at)";
        $stmt = $conn->prepare($sql);

        $created_at = date('Y-m-d');
        $stmt->bindParam(':content', $reply->content);
        $stmt->bindParam(':reply_to', $reply->reply_to);
        $stmt->bindParam(':user_id', $reply->user_id);
        $stmt->bindParam(':feedback_id', $reply->feedback_id);
        $stmt->bindParam(':created_at', $created_at);


        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User replied successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User replied failed"
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
