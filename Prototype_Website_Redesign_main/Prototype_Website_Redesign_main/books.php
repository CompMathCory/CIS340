<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch ($method) {
    case 'GET':
        // SMART QUERY: Rename DB columns to match React Props automatically
        $sql = "SELECT 
                    id, title, author, isbn, course, professor,
                    purchase_price as purchasePrice, 
                    rental_price as rentalPrice,
                    condition_type as `condition`,
                    inventory, 
                    is_required as required,
                    image_url as imageUrl
                FROM books WHERE 1=1";

        // Handle Search (For SearchPage.tsx)
        if (isset($_GET['search'])) {
            $term = "%" . $_GET['search'] . "%";
            $sql .= " AND (title LIKE '$term' OR course LIKE '$term' OR author LIKE '$term')";
        }
        
        $sql .= " ORDER BY id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert required from 1/0 to true/false for React
        foreach($result as &$book) {
            $book['required'] = (bool)$book['required'];
            $book['purchasePrice'] = (float)$book['purchasePrice'];
            $book['rentalPrice'] = (float)$book['rentalPrice'];
            $book['inventory'] = (int)$book['inventory'];
        }
        echo json_encode($result);
        break;

    case 'POST':
        // Create Book
        $sql = "INSERT INTO books (title, author, isbn, course, professor, purchase_price, rental_price, condition_type, inventory, is_required) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data->title, $data->author, $data->isbn, $data->course, $data->professor, 
            $data->purchasePrice, $data->rentalPrice, $data->condition, $data->inventory, $data->required ? 1 : 0
        ]);
        echo json_encode(["message" => "Created"]);
        break;

    case 'PUT':
        // Update Book
        $sql = "UPDATE books SET title=?, author=?, isbn=?, course=?, professor=?, purchase_price=?, rental_price=?, condition_type=?, inventory=?, is_required=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data->title, $data->author, $data->isbn, $data->course, $data->professor, 
            $data->purchasePrice, $data->rentalPrice, $data->condition, $data->inventory, $data->required ? 1 : 0, $data->id
        ]);
        echo json_encode(["message" => "Updated"]);
        break;

    case 'DELETE':
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(["message" => "Deleted"]);
        break;
}
?>