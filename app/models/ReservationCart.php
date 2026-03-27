<?php

class ReservationCart
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Create a new cart
     */
    public function createCart($sessionGuestID)
    {

        $result = $this->conn->execute_query(
            "INSERT INTO ReservationCarts (SessionGuestID, CreatedAt, ExpiresAt) VALUES (?, NOW(), NOW() + INTERVAL 30 MINUTE)",
            [$sessionGuestID]
        );

        if ($result) {
            return $this->conn->lastInsertId();
        } else {
            return false;
        }
    }


    public function getActiveCart($sessionGuestID)
    {
        $result = $this->conn->execute_query(
            "SELECT * 
             FROM ReservationCarts 
             WHERE SessionGuestID = ? 
             AND (ExpiresAt IS NULL OR ExpiresAt > NOW()) 
             ORDER BY CreatedAt DESC 
             LIMIT 1",
            [$sessionGuestID]
        );

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }


    public function findOrCreateCart($sessionToken)
    {
        $sessionGuestID = $this->getSessionGuestByToken($sessionToken);

        $result = $this->conn->execute_query(
            "SELECT CartID 
             FROM ReservationCarts 
             WHERE SessionGuestID = ? 
             AND (ExpiresAt IS NULL OR ExpiresAt > NOW()) 
             ORDER BY CreatedAt DESC 
             LIMIT 1",
            [$sessionGuestID]
        );

        if ($result ->num_rows > 0) {
            $cart = $this->getActiveCart($sessionGuestID);

            if ($cart) {
                return $cart['CartID'];
            }
        } else {
            return $this->createCart($sessionGuestID);
        }

    }


    public function cartExists($cartID)
    {

        $result = $this->conn->execute_query(
            "SELECT CartID 
             FROM ReservationCarts 
             WHERE CartID = ? 
             LIMIT 1",
            [$cartID]
        );
        if ($result->num_rows > 0) {
            return true;
        }
        return false;
    }


    public function deleteCart($cartID)
    {
        $result = $this->conn->execute_query(
            "DELETE FROM ReservationCarts WHERE CartID = ?",
            [$cartID]
        );
        if ($result->num_rows > 0) {
            return true;
        }
        return false;
    }

    public function extendCart($cartID)
    {
        $result = $this->conn->execute_query(
            "UPDATE ReservationCarts 
             SET ExpiresAt = DATE_ADD(NOW(), INTERVAL 1 DAY) 
             WHERE CartID = ?",
            [$cartID]
        );
        if ($result->num_rows > 0) {
            return true;
        }
        return false;
    }

    public function getSessionGuestByToken($sessionToken)
    {
        $result = $this->conn->execute_query(
            "SELECT SessionGuestID 
             FROM SessionGuests 
             WHERE SessionToken = ? 
             LIMIT 1",
            [$sessionToken]
        );
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['SessionGuestID'];
        }
        return null;
    }
}