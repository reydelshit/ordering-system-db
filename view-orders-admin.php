<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];

            $sql = "SELECT orders.total_amount, orders.created_at, order_products.user_id, order_products.order_id, product.product_name, product.product_price, product.product_image, order_products.quantity, order_status.status, order_status.status_id, order_products.product_id, orders.order_id
            FROM product
            LEFT JOIN order_products ON product.product_id = order_products.product_id
            LEFT JOIN order_status ON order_products.order_id = order_status.order_id
            LEFT JOIN orders ON order_status.order_id = orders.order_id
            WHERE order_products.order_id = :order_id
            GROUP BY product.product_id
            ORDER BY orders.order_id ASC";
        }


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);

            if (isset($order_id)) {
                $stmt->bindParam(':order_id', $order_id);
            }


            $stmt->execute();
            $order = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($order);
        }
        break;

    case "POST":
        $orders = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO orders (user_id, order_date, total_amount, payment_type) VALUES (:user_id, :order_date, :total_amount, :payment_type)";
        $stmt = $conn->prepare($sql);
        $order_date = date('Y-m-d');

        $stmt->bindParam(':user_id', $orders->user_id);
        $stmt->bindParam(':order_date', $order_date);
        $stmt->bindParam(':total_amount',  $orders->total_amount);
        $stmt->bindParam(':payment_type',  $orders->payment_type);

        if ($stmt->execute()) {
            $order_id = $conn->lastInsertId();

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
