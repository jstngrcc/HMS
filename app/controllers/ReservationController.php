<?php

require_once '../app/models/Reservation.php';
require_once '../app/models/Room.php';
require_once '../app/models/User.php';

class ReservationController
{
    public function submit()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'];
            $checkin = $_POST['checkin'];
            $checkout = $_POST['checkout'];
            $adults = (int) $_POST['adults'];
            $roomID = (int) $_POST['roomID'];
            $paymentMethod = (int) $_POST['paymentMethod'];
            $fname = $_POST['fname'];
            $lname = $_POST['lname'];
            $phone = $_POST['phone'];
            $birthDate = $_POST['birthDate'];

            if (empty($email) || empty($fname) || empty($lname) || empty($phone) || empty($birthDate)) {
                echo "Please fill in all required fields.";
                return;
            }

            if (empty($checkin) || empty($checkout)) {
                echo "Please select check-in and check-out dates.";
                return;
            } else if (strtotime($checkin) > strtotime($checkout)) {
                echo "Check-out date must be after check-in date.";
                return;
            } else if (strtotime($checkin) < strtotime(date('Y-m-d'))) {
                echo "Check-in date cannot be in the past.";
                return;
            }

            if ($adults < 1) {
                echo "At least 1 adult is required.";
                return;
            }

            $roomModel = new Room($GLOBALS['conn']);

            $available = $roomModel->checkRoomAvailability($roomID, $checkin, $checkout);

            if (!$available) {
                echo "Room is already booked for selected dates.";
                return;
            }

            $totalAmount = $roomModel->calculateTotalAmount(
                $roomModel->getRoomTypeName($roomID),
                $roomModel->getRoomPrice($roomID),
                $checkin,
                $checkout,
                $adults
            );

            $reservationModel = new Reservation($GLOBALS['conn']);

            $userModel = new User($GLOBALS['conn']);

            $guestID = $userModel->getGuestByEmail($email)->GuestID;

            if (!$guestID) {
                $guestID = $userModel->createGuest($email, $fname, $lname, $phone);
            }

            $reservations = $reservationModel->getGuestReservations($guestID);
            if ($reservations->num_rows > 0) {
                // Guest already has a reservation, add the room
                $reservationModel->addRoomToReservation($reservations->fetch_object()->ReservationID, $roomID);
                echo "Room added to existing reservation.";
            } else {
                // Guest has no reservation, create a new one
                $res = $reservationModel->createReservation(
                    $guestID,
                    $checkin,
                    $checkout,
                    $adults,
                    $roomID,
                    $paymentMethod,
                    $totalAmount
                );

                if ($res) {
                    $bookingToken = $res['BookingToken'] ?? 'N/A';
                    echo "Reservation created successfully! Your Booking Token: $bookingToken";
                } else {
                    echo "Failed to create reservation.";
                }
            }

            // DEBUG: show input values and their data types
            // echo "Debugging input values:<br>";
            // echo "guestID: " . $guestID . " (Type: " . gettype($guestID) . ")<br>";
            // echo "checkin: " . $checkin . " (Type: " . gettype($checkin) . ")<br>";
            // echo "checkout: " . $checkout . " (Type: " . gettype($checkout) . ")<br>";
            // echo "adults: " . $adults . " (Type: " . gettype($adults) . ")<br>";
            // echo "roomID: " . $roomID . " (Type: " . gettype($roomID) . ")<br>";
            // echo "paymentMethod: " . $paymentMethod . " (Type: " . gettype($paymentMethod) . ")<br>";
            // echo "totalAmount: " . $totalAmount . " (Type: " . gettype($totalAmount) . ")<br>";
        } else {
            echo "Invalid request method.";
        }
    }

    public function cart_submit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roomID = (int) $_POST['roomID'];
            $guests = (int) $_POST['guests'];
            $checkin = $_POST['checkin'];
            $checkout = $_POST['checkout'];

            // DEBUG: show input values and their data types
            // echo "roomID: " . $roomID . " (Type: " . gettype($roomID) . ")";
            // echo "guests: " . $guests . " (Type: " . gettype($guests) . ")";
            // echo "checkin: " . $checkin . " (Type: " . gettype($checkin) . ")";
            // echo "checkout: " . $checkout . " (Type: " . gettype($checkout) . ")";

            if (!$roomID || !$guests || !$checkin || !$checkout) {
                echo "All fields are required.";
                return;
            }

            $reservationModel = new Reservation($GLOBALS['conn']);
            $reservationModel->addRoomToCart($roomID, $checkin, $checkout, $guests);
        } else {
            echo "Invalid request method.";
        }
    }
}
?>