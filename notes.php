<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $sql = "SELECT * FROM notes WHERE order_id = :order_id";
        }


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($order_id)) {
                $stmt->bindParam(':order_id', $order_id);
            }

            $stmt->execute();
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);


            echo json_encode($notes);
        }


        break;

    case "POST":
        $notes = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO notes (order_id, notesMessage) VALUES (:order_id, :notesMessage)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bindParam(':order_id', $notes->order_id);
        $stmt->bindParam(':notesMessage', $notes->notesMessage);


        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "Admin notes successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Admin notes failed"
            ];
        }

        echo json_encode($response);
        break;
}
