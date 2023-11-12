<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['user_id'])) {
            $user_id_specific_user = $_GET['user_id'];

            $sql = "SELECT product.product_name, product.product_price, product.product_image, order_products.quantity, order_status.status, order_products.product_id, orders.order_id, orders.created_at
            FROM product
            LEFT JOIN order_products ON product.product_id = order_products.product_id
            LEFT JOIN order_status ON order_products.order_id = order_status.order_id
            LEFT JOIN orders ON order_status.order_id = orders.order_id
            WHERE order_products.user_id = :user_id
            GROUP BY order_products.product_id, orders.order_id
            ORDER BY orders.order_id DESC";
        }

        if (isset($_GET['product_id'])) {
            $product_id_user = $_GET['product_id'];
            $sql = "SELECT * FROM cart WHERE product_id = :product_id AND user_id = :user_id";
        }

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $sql = "SELECT * FROM order_products WHERE product_id = :product_id AND order_id = :order_id AND user_id = :user_id";
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

            if (isset($order_id)) {
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':user_id', $user_id_specific_user);
            }

            $stmt->execute();
            $sleep = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($sleep);
        }
        break;

    case "POST":
        $orders = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO orders (user_id, total_amount, payment_type, created_at) VALUES (:user_id, :total_amount, :payment_type, :created_at)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d H:i:s');

        $stmt->bindParam(':user_id', $orders->user_id);
        $stmt->bindParam(':total_amount',  $orders->total_amount);
        $stmt->bindParam(':payment_type',  $orders->payment_type);
        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            $order_id = $conn->lastInsertId();

            $sql7 = "SELECT user_id FROM users WHERE user_type = 'admin'";
            $stmt7 = $conn->prepare($sql7);
            $stmt7->execute();
            $adminUsers = $stmt7->fetchAll(PDO::FETCH_ASSOC);

            // Assuming $orders is an object with user_id property
            $created_at = date('Y-m-d H:i:s');
            $orderMessage = 'There is a new order, with order id: ' . $order_id . ' and total amount: ' . $orders->total_amount . ' and payment type: ' . $orders->payment_type;

            foreach ($adminUsers as $adminUser) {
                $sql6 = "INSERT INTO notification_message (sender_id, receiver_id, message, created_at) VALUES (:sender_id, :receiver_id, :message, :created_at)";
                $stmt6 = $conn->prepare($sql6);

                $stmt6->bindParam(':sender_id', $orders->user_id);
                $stmt6->bindParam(':receiver_id', $adminUser['user_id']);
                $stmt6->bindParam(':message', $orderMessage);
                $stmt6->bindParam(':created_at', $created_at);

                $stmt6->execute();
            }

            foreach ($orders->products as $product) {
                $sql = "INSERT INTO order_products (order_id, product_id, quantity, user_id) VALUES (:order_id, :product_id, :quantity, :user_id)";
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $product->product_id);
                $stmt->bindParam(':quantity', $product->qty);
                $stmt->bindParam(':user_id', $orders->user_id);

                if ($stmt->execute()) {
                    $sql2 = "UPDATE product SET quantity = quantity - :quantity WHERE product_id = :product_id";
                    $stmt2 = $conn->prepare($sql2);

                    $stmt2->bindParam(':quantity', $product->qty);
                    $stmt2->bindParam(':product_id', $product->product_id);


                    $stmt2->execute();

                    $sql3 = "UPDATE cart SET isPaid = 1 WHERE product_id = :product_id";
                    $stmt3 = $conn->prepare($sql3);
                    $stmt3->bindParam(':product_id', $product->product_id);


                    $stmt3->execute();

                    $sql4 = "INSERT INTO order_status (order_id, status) VALUES (:order_id, :status)";
                    $stmt4 = $conn->prepare($sql4);
                    $status = 'Pending';
                    $stmt4->bindParam(':order_id', $order_id);
                    $stmt4->bindParam(':status', $status);

                    $stmt4->execute();

                    $stmt5 = "INSERT INTO order_details (order_id, delivery_address, name, email, phone, payment_type, user_id) 
                                VALUES (:order_id, :delivery_address, :name, :email, :phone, :payment_type, :user_id)";

                    $stmt5 = $conn->prepare($stmt5);

                    $stmt5->bindParam(':order_id', $order_id);
                    $stmt5->bindParam(':delivery_address', $orders->delivery_address);
                    $stmt5->bindParam(':name', $orders->name);
                    $stmt5->bindParam(':email', $orders->email);
                    $stmt5->bindParam(':phone', $orders->phone);
                    $stmt5->bindParam(':payment_type', $orders->payment_type);
                    $stmt5->bindParam(':user_id', $orders->user_id);

                    $stmt5->execute();
                }
            }
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
}
