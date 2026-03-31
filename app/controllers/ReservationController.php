<?php

require_once '../app/models/User.php';
require_once '../app/models/Cart.php';
require_once '../app/models/Reservation.php';
require_once '../app/models/ReservationCart.php';

class ReservationController
{
    public function submit()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "error" => "Invalid request method."]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $guest = $input['guest'] ?? [];
        $paymentMethod = $input['paymentMethod'] ?? '';

        $paymentMap = [
            'Cash' => 1,
            'Card' => 2,
            'E-Wallet' => 3,
            'Bank' => 4
        ];

        $paymentMethodID = $paymentMap[$paymentMethod] ?? null;

        $email = trim($guest['email'] ?? '');
        $fname = trim($guest['fname'] ?? '');
        $lname = trim($guest['lname'] ?? '');
        $phone = trim($guest['phone'] ?? '');
        $birthDate = trim($guest['birthDate'] ?? '');
        $totalAmount = trim($input['totalAmount']);

        if (!$fname || !$lname || !$email || !$phone || !$birthDate || !$paymentMethod) {
            echo json_encode([
                "success" => false,
                "error" => "Incomplete guest information."
            ]);
            return;
        }

        $cartID = $_SESSION['cart_id'] ?? null;
        $sessionGuestID = $_SESSION['session_guest_id'] ?? null;

        if (!$cartID || !$sessionGuestID) {
            echo json_encode([
                "success" => false,
                "error" => "No active cart found."
            ]);
            return;
        }

        $userModel = new User($GLOBALS['conn']);
        $cartModel = new Cart($GLOBALS['conn']);
        $reservationModel = new Reservation($GLOBALS['conn']);

        try {
            if (!$_SESSION['logged_in_user_id']) {
                $guestIDObj = $userModel->createGuest($email, $fname, $lname, $phone, $birthDate);
                if (!$guestIDObj) {
                    echo json_encode([
                        'success' => false,
                        'error' => "Could not create guest."
                    ]);
                    return;
                }
                $guestID = is_object($guestIDObj) ? $guestIDObj->GuestID : $guestIDObj;
            } else {
                $guestIDObj = $userModel->getGuestIDbyUserID($_SESSION['logged_in_user_id']);
                $guestID = is_object($guestIDObj) ? $guestIDObj->GuestID : $guestIDObj;
            }
            $reservationData = $reservationModel->createReservation($guestID, $paymentMethodID, $totalAmount);

            $reservationID = $reservationData['ReservationID'] ?? null;
            $bookingToken = $reservationData['BookingToken'] ?? null;
            if (!$reservationID || !$bookingToken) {
                echo json_encode([
                    'success' => false,
                    'error' => "Reservation could not be made."
                ]);
            } else {
                $carts = $cartModel->getCartRows();

                foreach ($carts as $cart) {
                    $roomID = $cart['RoomID'];
                    $checkIn = $cart['CheckInDate'];
                    $checkOut = $cart['CheckOutDate'];
                    $numAdults = $cart['NumAdults'];

                    $result = $reservationModel->addRoomToReservation(
                        $reservationID,
                        $roomID,
                        $checkIn,
                        $checkOut,
                        $numAdults
                    );

                    if (!$result) {
                        echo json_encode([
                            "success" => false,
                            "error" => "Failed to add room to reservation."
                        ]);
                        return;
                    }
                }

                $cartsClear = $cartModel->removeCartItems($cartID);

                if (!$cartsClear) {
                    echo json_encode(["success" => false, "error" => "Failed to clear cart."]);
                    return;
                }

                echo json_encode([
                    "success" => true,
                    "message" => "Reservation completed successfully."
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "error" => "Reservation failed: " . $e->getMessage()
            ]);
        }
    }
}
?>