<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lance Hotel Admin</title>
  <link href="/css/output.css" rel="stylesheet">
  <link href="/css/admin/admin.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@8.0.13/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@8.0.13/dist/ionicons/ionicons.js"></script>
</head>

<body class="bg-stone-200 font-roboto flex flex-col min-h-screen">
  <!-- Sidebar -->
  <?php include_once __DIR__ . '/components/sidebar.view.php'; ?>

  <!-- Top Bar -->
  <?php include_once __DIR__ . '/components/header.view.php'; ?>

  <!-- Main Content -->
  <main class="ml-64 mt-15.5 p-4">

    <div class="flex flex-col gap-4 justify-between">
      <?php include_once __DIR__ . '/components/dashboard/Bookings.view.php'; ?>

      <?php include_once __DIR__ . '/components/dashboard/RoomAvailability.view.php'; ?>

      <?php include_once __DIR__ . '/components/dashboard/Reservations.view.php'; ?>

      <div class="flex justify-between gap-4">
        <?php include_once __DIR__ . '/components/dashboard/BookingbyPlatform.view.php'; ?>

        <?php include_once __DIR__ . '/components/dashboard/TopPerforming.view.php'; ?>
      </div>
    </div>
  </main>
</body>

</html>