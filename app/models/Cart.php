<?php

class Cart
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function addRoomToCart($roomNumber, $checkin, $checkout, $adults)
    {
        $CartID = $_SESSION['cart_id'];

        $stmt = $this->conn->execute_query(
            "SELECT RoomID FROM Rooms WHERE RoomNumber = ?",
            [$roomNumber]
        );

        $row = $stmt->fetch_assoc();

        if (!$row) {
            throw new Exception("Room not found.");
        }

        $roomID = $row['RoomID'];

        $result = $this->conn->execute_query(
            "CALL AddRoomToCart(?, ?, ?, ?, ?)",
            [$CartID, $roomID, $checkin, $checkout, $adults]
        );

        if ($result) {
            return true;
        } else {
            throw new Exception("Failed to add to cart.");
        }
    }

    public function getCartAmount()
    {
        $CartID = $_SESSION["cart_id"];

        if (!$CartID) {
            return 0;
        }

        $result = $this->conn->execute_query("
                SELECT COUNT(*) as total
                FROM CartRooms WHERE CartID = ?",
            [$CartID]
        );

        return $result->fetch_assoc()['total'] ?? 0;
    }
}

?>