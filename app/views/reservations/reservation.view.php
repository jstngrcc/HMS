<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
</head>
<body>
    <form method="POST" action="/reservation-submit">
    
        <label for="email">Email: </label>
        <input type="email" name="email" id="email" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>
        <label for="checkin_date">Check-in:</label>
        <input type="date" id="checkin_date" name="checkin" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>
        <label for="checkout_date">Check-out:</label>
        <input type="date" id="checkout_date" name="checkout" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>
        <label for="adults">Adults:</label>
        <input type="number" id="adults" name="adults" min="1" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>
        <label for="roomID">Room ID:</label>
        <input type="number" id="roomID" name="roomID" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>
        <label for="payment_method">Payment: </label>
        <select name="paymentMethod" id="paymentMethod" required class="border border-gray-300 p-2 rounded w-full text-black bg-white">
            <option value=1>cash</option>
            <option value=2>credit card</option>
            <option value=3>debit card</option>
            <option value=4>online payment</option>
        </select><br>

        <label for="fname">First Name: </label>
        <input type="text" id="fname" name="fname" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>
        <label for="lname">Last Name: </label>
        <input type="text" id="lname" name="lname" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>
        <label for="phone">Phone Contact: </label>
        <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="phone" placeholder="000-000-0000" required class="border border-gray-300 p-2 rounded w-full text-black bg-white"><br>

        <button type="submit">Book Room</button><br>

    </form>
</body>
</html>