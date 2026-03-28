<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HMS";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Collect search filters
$checkin = $_POST['checkin'] ?? null;
$checkout = $_POST['checkout'] ?? null;
$guests = $_POST['guests'] ?? null;
$room_type = $_POST['room_type'] ?? '';
$bed_count = $_POST['bed_count'] ?? '';
$sort_by = $_POST['sort_by'] ?? 'r.RoomNumber';

$rooms = [];
$params = [];
$types = '';
$where = "WHERE r.Status = 'available'";

// Filter by room type
if ($room_type) {
    $where .= " AND rt.RoomTypeID = ?";
    $params[] = $room_type;
    $types .= 'i';
}

// Filter by bed count
if ($bed_count) {
    $where .= " AND rt.BedCount = ?";
    $params[] = $bed_count;
    $types .= 'i';
}

// Filter by number of guests
if ($guests) {
    $where .= " AND rt.MaxOccupancy >= ?";
    $params[] = $guests;
    $types .= 'i';
}

// Filter by check-in/check-out
$check_sql = "";
if ($checkin && $checkout) {
    $check_sql = " AND r.RoomID NOT IN (
        SELECT rr.RoomID
        FROM ReservationRooms rr
        JOIN Reservations res ON rr.ReservationID = res.ReservationID
        WHERE NOT (res.CheckOutDate <= ? OR res.CheckInDate >= ?)
    )";
    $params[] = $checkin;
    $params[] = $checkout;
    $types .= 'ss';
}

$sql = "SELECT r.RoomID, r.RoomNumber, rt.RoomTypeName, rt.BasePrice, rt.MaxOccupancy, bt.BedName, rt.BedCount
        FROM Rooms r
        JOIN RoomTypes rt ON r.RoomTypeID = rt.RoomTypeID
        LEFT JOIN BedTypes bt ON rt.BedTypeID = bt.BedTypeID
        $where
        $check_sql
        ORDER BY $sort_by ASC";

$stmt = $conn->prepare($sql);

// Bind parameters dynamically
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $rooms[] = $row;
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Advanced Room Search</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Search Rooms</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php if ($_POST && empty($rooms)): ?>
            <p class="text-red-500">No rooms match your criteria.</p>
        <?php endif; ?>

        <?php foreach ($rooms as $room): ?>
            <div class="p-4 border rounded shadow bg-gray-50">
                <h2 class="font-bold text-lg">Room <?= htmlspecialchars($room['RoomNumber']) ?> - <?= htmlspecialchars($room['RoomTypeName']) ?></h2>
                <p>Beds: <?= htmlspecialchars($room['BedCount']) ?> x <?= htmlspecialchars($room['BedName'] ?? 'Standard') ?></p>
                <p>Max Occupancy: <?= htmlspecialchars($room['MaxOccupancy']) ?></p>
                <p>Price: $<?= htmlspecialchars($room['BasePrice']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>