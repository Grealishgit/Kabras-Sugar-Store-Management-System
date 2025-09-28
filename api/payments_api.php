<?php
session_start();
require_once __DIR__ . '/../handlers/PaymentsHandler.php';

$handler = new PaymentsHandler();

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'get_customers':
        echo json_encode($handler->getCustomers());
        break;

    case 'add_payment':
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $_SESSION['user_id'] ?? null; // get from session
        $customer_id = $data['customer_id'] ?? null;
        $sale_id = $data['sale_id'] ?? null;
        $amount = $data['amount'] ?? null;
        $method = $data['method'] ?? null;
        $reference_number = $data['reference_number'] ?? null;
        $notes = $data['notes'] ?? null;

        if ($customer_id && $amount && $method && $user_id) {
            $success = $handler->addPayment(
                $customer_id,
                $amount,
                $method,
                $user_id,
                $sale_id,
                $reference_number,
                $notes
            );
            echo json_encode(["success" => $success]);
        } else {
            echo json_encode(["success" => false, "message" => "Missing fields or user not logged in"]);
        }
        break;


    default:
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
}
