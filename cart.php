<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['user_id'])) {
            $user_id_specific_user = $_GET['user_id'];
            $sql = "SELECT cart.cart_id, product.product_id, product.product_price, product.product_image, product.product_name, cart.qty FROM 
            ((cart LEFT JOIN product ON cart.product_id = product.product_id) 
            LEFT JOIN users ON cart.user_id = users.user_id) WHERE (cart.user_id = :user_id AND isPaid = 0)";
        }

        if (isset($_GET['product_id'])) {
            $product_id_user = $_GET['product_id'];
            $sql = "SELECT * FROM cart WHERE product_id = :product_id AND user_id = :user_id AND isPaid = 0";
        }


        if (!isset($_GET['user_id']) && !isset($_GET['product_id'])) {
            $sql = "SELECT * FROM product ORDER BY product_id DESC";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($user_id_specific_user)) {
                $stmt->bindParam(':user_id', $user_id_specific_user);
            }

            if (isset($product_id_user)) {
                $stmt->bindParam(':product_id', $product_id_user);
                $stmt->bindParam(':user_id', $user_id_specific_user);
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
        $cart = json_decode(file_get_contents('php://input'));

        $sql = "UPDATE cart SET qty=:qty WHERE cart_id = :cart_id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':cart_id', $cart->cart_id);
        $stmt->bindParam(':qty', $cart->qty);

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

        break;

    case "DELETE":
        $sql = "DELETE FROM cart WHERE cart_id = :cart_id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cart_id', $path[3]);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "cart deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "cart deletion failed"
            ];
        }
}
