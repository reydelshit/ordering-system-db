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
}
