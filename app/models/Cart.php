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

        // Get RoomID from RoomNumber
        $stmt = $this->conn->execute_query(
            "SELECT RoomID FROM Rooms WHERE RoomNumber = ?",
            [$roomNumber]
        );

        $row = $stmt->fetch_assoc();

        if (!$row) {
            throw new Exception("Room not found.");
        }

        $roomID = $row['RoomID'];

        // Check for overlapping dates in the cart
        $stmt = $this->conn->execute_query(
            "SELECT 1 
         FROM CartRooms 
         WHERE CartID = ? AND RoomID = ? 
           AND NOT (CheckOutDate <= ? OR CheckInDate >= ?)",
            [$CartID, $roomID, $checkin, $checkout]
        );

        if ($stmt->num_rows > 0) {
            throw new Exception("This room is already in your cart for overlapping dates.");
        }

        // Call stored procedure
        try {
            $this->conn->execute_query(
                "CALL AddRoomToCart(?, ?, ?, ?, ?)",
                [$CartID, $roomID, $checkin, $checkout, $adults]
            );
            return true;
        } catch (mysqli_sql_exception $e) {
            throw new Exception("Failed to add to cart: " . $e->getMessage());
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

    public function getCartRows()
    {
        $CartID = $_SESSION["cart_id"];

        if (!$CartID) {
            return 0;
        }

        $result = $this->conn->execute_query(
            "SELECT cr.CartRoomID, r.RoomID, r.RoomNumber, rt.RoomTypeName, rt.BasePrice, cr.NumAdults, cr.CheckInDate, cr.CheckOutDate
            FROM CartRooms cr
            INNER JOIN Rooms r ON cr.RoomID = r.RoomID
            INNER JOIN RoomTypes rt ON r.RoomTypeID = rt.RoomTypeID
            WHERE cr.CartID = ?",
            [$CartID]
        );

        return $result->fetch_all(MYSQLI_ASSOC);

    }

    public function removeCartItem(int $cartRoomID)
    {
        $CartID = $_SESSION['cart_id'] ?? null;
        if (!$CartID) {
            throw new Exception("No cart found for session.");
        }

        try {
            $this->conn->execute_query(
                "DELETE FROM CartRooms WHERE CartRoomID = ? AND CartID = ?",
                [$cartRoomID, $CartID]
            );
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to remove cart item: " . $e->getMessage());
        }
    }

    public function removeCartItems(int $cartRoomID) {
        $CartID = $_SESSION['cart_id'] ?? null;
        if (!$CartID) {
            throw new Exception("No cart found for session.");
        }

        try {
            $this->conn->execute_query(
                "DELETE FROM CartRooms"
            );
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to clear cart: " . $e->getMessage());
        }
    }
}

?>