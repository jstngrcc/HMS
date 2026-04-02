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

        $totalBeforeDiscount = floatval($input['totalBeforeDiscount'] ?? 0); // new
        $discountAmount = floatval($input['discountAmount'] ?? 0);           // new

        // Extract guest info
        $guest = $input['guest'] ?? [];
        $paymentMethod = $input['paymentMethod'] ?? '';
        $paymentMap = ['Cash' => 1, 'Card' => 2, 'E-Wallet' => 3, 'Bank' => 4];
        $paymentMethodID = $paymentMap[$paymentMethod] ?? null;

        $email = trim($guest['email'] ?? '');
        $fname = trim($guest['fname'] ?? '');
        $lname = trim($guest['lname'] ?? '');
        $country = trim($guest['country_code'] ?? '');
        $phoneNo = trim($guest['phone'] ?? '');
        $phone = $country . $phoneNo;
        $birthDate = trim($guest['birthDate'] ?? '');
        $totalAmount = trim($input['totalAmount'] ?? 0);

        // Extract discount info
        $discountType = $input['discountType'] ?? null; // e.g., "Senior", "PWD"
        $discountValue = floatval($input['discountValue'] ?? 0); // numeric percentage or amount
        $discountCardNumber = $input['discountCardNumber'] ?? null;

        if (!$fname || !$lname || !$email || !$phone || !$birthDate || !$paymentMethodID) {
            echo json_encode(["success" => false, "error" => "Incomplete guest information."]);
            return;
        }

        $cartID = $_SESSION['cart_id'] ?? null;
        $sessionGuestID = $_SESSION['session_guest_id'] ?? null;

        if (!$cartID || !$sessionGuestID) {
            echo json_encode(["success" => false, "error" => "No active cart found."]);
            return;
        }

        $userModel = new User($GLOBALS['conn']);
        $cartModel = new Cart($GLOBALS['conn']);
        $reservationModel = new Reservation($GLOBALS['conn']);

        try {
            // Determine guest ID
            if (!isset($_SESSION['logged_in_user_id'])) {
                $guestIDObj = $userModel->createGuest($email, $fname, $lname, $phone, $birthDate);
                $guestID = $guestIDObj->GuestID ?? $guestIDObj;
            } else {
                $currentUserGuestIDObj = $userModel->getGuestIDbyUserID($_SESSION['logged_in_user_id']);
                $currentUserEmail = $userModel->getGuestEmailByID($currentUserGuestIDObj->GuestID);

                if ($email !== $currentUserEmail) {
                    $newGuest = $userModel->createGuest($email, $fname, $lname, $phone, $birthDate);
                    if (is_object($newGuest) && isset($newGuest->GuestID)) {
                        $guestID = $newGuest->GuestID;
                    } elseif (is_int($newGuest) || is_string($newGuest)) {
                        $guestID = $newGuest;
                    } else {
                        throw new Exception("Failed to create guest. GuestID is null.");
                    }
                } else {
                    $guestID = $currentUserGuestIDObj->GuestID;
                }
            }

            // Fetch cart rows
            $cartRows = $cartModel->getCartRows();

            if (empty($cartRows)) {
                echo json_encode(["success" => false, "error" => "Cart is empty."]);
                return;
            }

            $reservationData = $reservationModel->bookRoomsAtomic(
                $guestID,
                $paymentMethodID,
                $totalBeforeDiscount,   // total before discount
                $cartRows,
                $discountType,
                $discountAmount,        // now passing discount amount
                $discountCardNumber
            );

            // Link reservation to logged-in user
            if (isset($_SESSION['logged_in_user_id'])) {
                $reservationModel->linkReservationToUser($reservationData['ReservationID'], $_SESSION['logged_in_user_id']);
            }

            // Remove cart items
            $cartModel->removeCartItems($cartID);

            // Send reservation confirmation
            $confirmationEmail = isset($_SESSION['logged_in_user_id'])
                ? $userModel->getGuestEmailByID($currentUserGuestIDObj->GuestID)
                : $email;

            $reservationModel->sendReservationConfirmation($confirmationEmail, $reservationData['BookingToken']);

            echo json_encode([
                "success" => true,
                // "message" => "Reservation completed successfully.",
                "message" => "$paymentMethodID $totalBeforeDiscount $discountType $discountAmount $discountCardNumber",
                "BookingToken" => $reservationData['BookingToken']
            ]);

        } catch (Exception $e) {
            if ($e->getMessage() === 'Room is already booked for these dates.') {
                $removedItems = $cartModel->removeUnavailableCartItems();
                echo json_encode([
                    "success" => false,
                    "error" => "Some rooms in your cart are no longer available.",
                    "removedItems" => $removedItems
                ]);
                return;
            }

            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }

    public function show($bookingToken)
    {
        $reservationModel = new Reservation($GLOBALS["conn"]);
        $reservationData = $reservationModel->getReservationWithGuest($bookingToken);

        if (empty($reservationData)) {
            require_once __DIR__ . '/../views/static/404.view.php';
            return;
        }

        $reservationID = $reservationData[0]['ReservationID'];
        $userID = $_SESSION["logged_in_user_id"] ?? null;

        $ownsReservation = $reservationModel->checkUserReservation($userID, $reservationID);

        if (!$ownsReservation) {
            require_once __DIR__ . '/../views/static/404.view.php';
            return;
        }

        // Extract guest info from first row
        $guestDetails = [
            'FullName' => $reservationData[0]['FirstName'] . ' ' . $reservationData[0]['LastName'],
            'Email' => $reservationData[0]['Email'],
            'PhoneContact' => $reservationData[0]['PhoneContact'],
            'BookingToken' => $reservationData[0]['BookingToken'],
            'BookingDate' => date('F j, Y', strtotime($reservationData[0]['BookingDate'])),
        ];
        $payment = $reservationModel->getReservationPayment($reservationID); // new method
        $rooms = $reservationData;

        require __DIR__ . '/../views/reservations/reservation-details.view.php';
    }

    public function cancel()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "success" => false,
                "error" => "Invalid request method."
            ]);
            exit;
        }

        if (!isset($_SESSION['logged_in_user_id'])) {
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized. Please log in."
            ]);
            exit;
        }

        $bookingToken = $_POST['booking_token'] ?? null;

        if (!$bookingToken) {
            echo json_encode([
                "success" => false,
                "error" => "Missing booking token."
            ]);
            exit;
        }

        try {
            $reservationModel = new Reservation($GLOBALS['conn']);
            $userModel = new User($GLOBALS['conn']);

            // Call your model method that executes the CancelReservation procedure
            $currentUserGuestIDObj = $userModel->getGuestIDbyUserID($_SESSION['logged_in_user_id']);
            $reservationModel->cancelReservation($bookingToken, $currentUserGuestIDObj->GuestID);

            echo json_encode([
                "success" => true,
                "message" => "Reservation cancelled successfully."
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
            exit;
        }
    }
    public function cancelGuest()
    {
        header('Content-Type: application/json');

        $bookingToken = $_POST['booking_token'] ?? null;

        if (!$bookingToken) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing booking token.'
            ]);
            exit;
        }

        $reservationModel = new Reservation($GLOBALS['conn']);
        $reservation = $reservationModel->getGuestReservationByToken($bookingToken);

        if (!$reservation) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or non-cancellable reservation.'
            ]);
            exit;
        }

        try {
            // If userId is null, treat as guest cancel
            $reservationModel->cancelReservationGuest($bookingToken);

            echo json_encode([
                'success' => true,
                'message' => 'Reservation cancelled successfully.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to cancel reservation: ' . $e->getMessage()
            ]);
        }
    }


    public function showGuestCancelForm($bookingToken)
    {
        $reservationModel = new Reservation($GLOBALS['conn']);
        $reservation = $reservationModel->getGuestReservationByToken($bookingToken);

        if (!$reservation) {
            header('Location: /bookings');
            exit;
        }

        // pass $reservation array to the view
        require_once '../app/views/reservations/guest-cancel.view.php';
    }
}
?>