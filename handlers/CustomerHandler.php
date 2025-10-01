<?php
require_once __DIR__ . '/../app/models/Customer.php';

class CustomerHandler
{
    private $customerModel;

    public function __construct()
    {
        $this->customerModel = new Customer();
    }

    public function getAllCustomers()
    {
        return $this->customerModel->getAllCustomers();
    }

    public function getCustomerById($id)
    {
        return $this->customerModel->getCustomerById($id);
    }

    public function createCustomer($data)
    {
        // Validate required fields
        if (empty($data['name'])) {
            return ['success' => false, 'error' => 'Customer name is required'];
        }

        try {
            $result = $this->customerModel->createCustomer($data);
            if ($result) {
                return ['success' => true, 'message' => 'Customer created successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to create customer'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateCustomer($id, $data)
    {
        // Validate required fields
        if (empty($data['name'])) {
            return ['success' => false, 'error' => 'Customer name is required'];
        }

        try {
            $result = $this->customerModel->updateCustomer($id, $data);
            if ($result) {
                return ['success' => true, 'message' => 'Customer updated successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to update customer'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteCustomer($id)
    {
        try {
            $result = $this->customerModel->deleteCustomer($id);
            if ($result) {
                return ['success' => true, 'message' => 'Customer deleted successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to delete customer'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function searchCustomers($search)
    {
        if (empty($search)) {
            return $this->getAllCustomers();
        }
        return $this->customerModel->searchCustomers($search);
    }

    public function filterCustomers($type = null, $status = null)
    {
        return $this->customerModel->filterCustomers($type, $status);
    }

    public function getCustomerStats()
    {
        return $this->customerModel->getCustomerStats();
    }

    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'create':
                    $data = [
                        'name' => $_POST['name'] ?? '',
                        'email' => $_POST['email'] ?? '',
                        'phone' => $_POST['phone'] ?? '',
                        'address' => $_POST['address'] ?? '',
                        'town' => $_POST['town'] ?? '',
                        'type' => $_POST['type'] ?? 'individual',
                        'status' => $_POST['status'] ?? 'active',
                        'notes' => $_POST['notes'] ?? ''
                    ];
                    return $this->createCustomer($data);

                case 'update':
                    $id = $_POST['id'] ?? '';
                    $data = [
                        'name' => $_POST['name'] ?? '',
                        'email' => $_POST['email'] ?? '',
                        'phone' => $_POST['phone'] ?? '',
                        'address' => $_POST['address'] ?? '',
                        'town' => $_POST['town'] ?? '',
                        'type' => $_POST['type'] ?? 'individual',
                        'status' => $_POST['status'] ?? 'active',
                        'notes' => $_POST['notes'] ?? ''
                    ];
                    return $this->updateCustomer($id, $data);

                case 'delete':
                    $id = $_POST['id'] ?? '';
                    return $this->deleteCustomer($id);

                default:
                    return ['success' => false, 'error' => 'Invalid action'];
            }
        }

        return ['success' => false, 'error' => 'Invalid request method'];
    }
}
