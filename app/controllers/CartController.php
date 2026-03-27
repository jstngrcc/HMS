<?php

require_once '../app/models/Reservation.php';
require_once '../app/models/Room.php';
require_once '../app/models/User.php';
require_once '../app/models/Cart.php';

class CartController
{
    public function initCart()
    {

    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // This adds the room to the cart, but doesn't actually create a reservation until the user clicks "Submit Reservation"
            $adults = (int) $_POST['adults'];
            $roomID = (int) $_POST['room'];

            $dateRange = $_POST['checkin'];

            if (empty($dateRange)) {
                echo "Please select check-in and check-out dates.";
                return;
            }

            // Split the range
            $dates = explode(" to ", $dateRange);

            if (count($dates) !== 2) {
                echo "Invalid date range.";
                return;
            }

            // Convert using DateTime (SAFE for d/m/Y)
            $checkinObj = DateTime::createFromFormat('d/m/Y', trim($dates[0]));
            $checkoutObj = DateTime::createFromFormat('d/m/Y', trim($dates[1]));

            if (!$checkinObj || !$checkoutObj) {
                echo "Invalid date format.";
                return;
            }

            // Convert to Y-m-d (for DB or comparisons)
            $checkin = $checkinObj->format('Y-m-d');
            $checkout = $checkoutObj->format('Y-m-d');

            $today = new DateTime();
            $today->setTime(0, 0, 0); // Set to midnight for accurate comparison
            $checkinObj->setTime(0, 0, 0);
            $checkoutObj->setTime(0, 0, 0);

            if ($checkinObj > $checkoutObj) {
                echo "Check-out date must be after check-in date.";
                return;
            }

            if ($checkinObj < $today) {
                echo "Check-in date cannot be in the past.";
                return;
            }

            if (empty($adults)) {
                echo "Please input at least 1 guest.";
                return;
            }

            if (empty($roomID)) {
                echo "Please select a room.";
                return;
            }

            $cart = new Cart($GLOBALS['conn']);

        }
    }
}
?>