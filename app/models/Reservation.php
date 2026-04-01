<?php

class Reservation
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function areRoomsAvailable($carts)
    {
        foreach ($carts as $cart) {
            $roomID = $cart['RoomID'];
            $checkIn = $cart['CheckInDate'];
            $checkOut = $cart['CheckOutDate'];

            // Clear all result sets
            while ($this->conn->more_results()) {
                $this->conn->next_result();
            }

            $this->conn->execute_query("CALL CheckRoomAvailability(?, ?, ?, @isAvailable)", [$roomID, $checkIn, $checkOut]);

            // Fetch and free all result sets returned by the procedure
            do {
                if ($res = $this->conn->store_result()) {
                    $res->free();
                }
            } while ($this->conn->more_results() && $this->conn->next_result());

            $result = $this->conn->query("SELECT @isAvailable AS isAvailable;");
            $row = $result->fetch_assoc();
            $result->free();

            if ((int) $row['isAvailable'] === 0) {
                return [
                    'success' => false,
                    'roomID' => $roomID,
                    'message' => 'Room ' . $roomID . ' is already booked for these dates'
                ];
            }
        }

        return ['success' => true];
    }

    public function createReservation($guestID, $paymentMethod, $totalAmount)
    {
        try {
            $this->conn->execute_query(
                "CALL CreateReservation(?, ?, ?, @ReservationID, @BookingToken)",
                [$guestID, $paymentMethod, $totalAmount]
            );

            $result = $this->conn->query("SELECT @ReservationID AS ReservationID, @BookingToken AS BookingToken;");


            if ($result) {
                $row = $result->fetch_assoc();
                $result->free();
                return [
                    'ReservationID' => $row['ReservationID'],
                    'BookingToken' => $row['BookingToken']
                ];
            } else {
                throw new Exception("Failed to create reservation: " . $this->conn->error);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to create reservation: " . $e->getMessage());
        }
    }

    public function bookRoomsAtomic($guestID, $paymentMethodID, $totalAmount, $cartRows)
    {
        $cartJSON = json_encode($cartRows);

        // Call procedure using execute_query
        $this->conn->execute_query(
            "CALL BookRoomsAtomic(?, ?, ?, ?, @ReservationID, @BookingToken, @Success, @Message)",
            [$guestID, $paymentMethodID, $totalAmount, $cartJSON]
        );

        $result = $this->conn->query("SELECT @ReservationID AS ReservationID, @BookingToken AS BookingToken, @Success AS Success, @Message AS Message");
        $row = $result->fetch_assoc();
        $result->free();

        if (!(bool) $row['Success']) {
            if (empty($row['Mesage'])) {
                throw new Exception('Room is already booked for these dates.');
            } else {
                throw new Exception($row['message']);
            }
        }

        return [
            'ReservationID' => $row['ReservationID'],
            'BookingToken' => $row['BookingToken']
        ];
    }

    public function addRoomToReservation($reservationID, $roomID, $checkIn, $checkOut, $numAdults)
    {
        // Call procedure with OUT params
        $this->conn->execute_query(
            "CALL AddRoomToReservation(?, ?, ?, ?, ?, @success, @message)",
            [$reservationID, $roomID, $checkIn, $checkOut, $numAdults]
        );

        // Fetch OUT params
        $result = $this->conn->query("SELECT @success AS success, @message AS message;");
        if ($result) {
            $row = $result->fetch_assoc();
            $result->free();

            if (!$row['success']) {
                throw new Exception($row['message']);  // <-- THIS throws and triggers rollback
            }

            return true;
        } else {
            throw new Exception('Failed to retrieve procedure output: ' . $this->conn->error);
        }
    }
    public function cancelReservation($reservationID = null, $bookingToken = null)
    {
        try {
            if ($bookingToken) {
                $result = $this->conn->execute_query(
                    "CALL CancelReservationGuest(?)",
                    [$bookingToken]
                );
            } elseif ($reservationID) {
                $result = $this->conn->execute_query(
                    "CALL CancelReservation(?)",
                    [$reservationID]
                );
            } else {
                throw new Exception("Either reservation ID or booking token must be provided.");
            }

            if (!$result) {
                throw new Exception("Failed to cancel reservation: " . $this->conn->error);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to cancel reservation: " . $e->getMessage());
        }
    }

    public function getGuestReservations($GuestID)
    {
        try {
            $result = $this->conn->execute_query(
                "SELECT * FROM Reservations WHERE GuestID = ?",
                [$GuestID]
            );
            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to cancel reservation: " . $e->getMessage());
        }
    }

    public function getGuestReservationByToken($bookingToken)
    {
        try {
            $result = $this->conn->execute_query(
                "SELECT * FROM Reservations WHERE BookingToken = ?",
                [$bookingToken]
            );
            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to cancel reservation: " . $e->getMessage());
        }
    }

    public function addRoomToCart($roomID, $checkin, $checkout, $adults)
    {
        try {
            $result = $this->conn->execute_query(
                "CALL AddRoomToCart(?, ?, ?, ?)",
                [$roomID, $checkin, $checkout, $adults]
            );

            if (!$result) {
                throw new Exception("Failed to add room to cart: " . $this->conn->error);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to cancel reservation: " . $e->getMessage());
        }
    }

    public function showReservations()
    {
        try {
            $userID = $_SESSION["logged_in_user_id"];

            $result = $this->conn->execute_query(
                "SELECT
                r.ReservationID,
                r.BookingToken,
                rs.StatusName AS ReservationStatus,
                pm.MethodName AS PaymentMethod,
                p.Amount,
                p.PaymentDate,
                r.CreatedAt,
                g.FirstName AS GuestFirstName,
                g.LastName AS GuestLastName,
                g.Email AS GuestEmail
            FROM Reservations r
            INNER JOIN Guests g ON r.GuestID = g.GuestID
            INNER JOIN ReservationStatus rs ON r.StatusID = rs.StatusID
            LEFT JOIN Payments p ON r.ReservationID = p.ReservationID
            LEFT JOIN PaymentMethods pm ON p.MethodID = pm.MethodID
            INNER JOIN UserReservations ur ON r.ReservationID = ur.ReservationID
            WHERE ur.UserID = ?",
                [$userID]
            );

            if (!$result) {
                throw new Exception("Error retrieving reservations: " . $this->conn->error);
            }

            if ($result->num_rows === 0) {
                return [];
            }

            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            throw new Exception("Error retrieving reservations: " . $e->getMessage());
        }
    }

    public function getReservationsForUser($userID)
    {
        $result = $this->conn->execute_query(
            "SELECT r.* 
         FROM Reservations r
         JOIN UserReservations ur ON r.ReservationID = ur.ReservationID
         WHERE ur.UserID = ?",
            [$userID]
        );

        $reservations = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $reservations[] = $row;
            }
        }

        return $reservations;
    }
    public function linkReservationToUser($reservationID, $userID)
    {
        $result = $this->conn->execute_query(
            "INSERT INTO UserReservations (UserID, ReservationID) VALUES (?, ?)",
            [$userID, $reservationID]
        );

        if (!$result) {
            throw new Exception("Failed to link reservation to user: " . $this->conn->error);
        }

        return true;
    }

    public function isReservationLinkedToUser($reservationID, $userID)
    {
        $result = $this->conn->execute_query(
            "SELECT 1 FROM UserReservations WHERE ReservationID = ? AND UserID = ?",
            [$reservationID, $userID]
        );

        return $result && $result->num_rows > 0;
    }

    public function findByToken($token)
    {
        $result = $this->conn->execute_query(
            "SELECT * FROM Reservations WHERE BookingToken = ?",
            [$token]
        );

        return $result->fetch_assoc();
    }

    public function getReservationRooms($token)
    {
        $result = $this->conn->execute_query(
            "SELECT
                res.ReservationID,
                rt.RoomTypeName AS RoomType,
                r.RoomNumber,
                rr.NumAdults AS NumGuests,
                rt.BasePrice,
                rr.CheckInDate,
                rr.CheckOutDate
            FROM Reservations res
            JOIN ReservationRooms rr ON rr.ReservationID = res.ReservationID
            JOIN Rooms r ON r.RoomID = rr.RoomID
            JOIN RoomTypes rt ON rt.RoomTypeID = r.RoomTypeID
            WHERE res.BookingToken = ?",
            [$token]
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function checkUserReservation($userID, $reservationID)
    {
        $result = $this->conn->execute_query(
            "SELECT 1 FROM UserReservations WHERE UserID = ? AND ReservationID = ? LIMIT 1",
            [$userID, $reservationID]
        );
        return $result && $result->num_rows > 0;
    }

    public function getReservationWithGuest($token)
    {
        $result = $this->conn->execute_query(
            "SELECT
            res.ReservationID,
            res.BookingToken,
            res.CreatedAt AS BookingDate,
            g.FirstName,
            g.LastName,
            g.Email,
            g.PhoneContact,
            rr.CheckInDate,
            rr.CheckOutDate,
            rr.NumAdults AS NumGuests,
            r.RoomNumber,
            rt.RoomTypeName AS RoomType,
            rt.BasePrice
        FROM Reservations res
        JOIN Guests g ON g.GuestID = res.GuestID
        JOIN ReservationRooms rr ON rr.ReservationID = res.ReservationID
        JOIN Rooms r ON r.RoomID = rr.RoomID
        JOIN RoomTypes rt ON rt.RoomTypeID = r.RoomTypeID
        WHERE res.BookingToken = ?",
            [$token]
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getReservationPayment($reservationID)
    {
        $result = $this->conn->execute_query(
            "SELECT p.PaymentID, pm.MethodName AS PaymentMethod, p.PaymentStatus
         FROM Payments p
         JOIN PaymentMethods pm ON pm.MethodID = p.MethodID
         WHERE p.ReservationID = ? LIMIT 1",
            [$reservationID]
        );
        return $result->fetch_assoc();
    }
}