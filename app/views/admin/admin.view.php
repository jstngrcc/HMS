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

  <!-- Page toggle -->
  <script>
    // Toggle between pages
    function show(id) {
      document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
      document.getElementById(id).classList.add('active');
    }

    // Revenue chart (line)
    new Chart(document.getElementById('revenueChart'), {
      type: 'line',
      data: {
        labels: ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'],
        datasets: [{
          label: 'Revenue',
          data: [200000, 250000, 315060, 280000, 290000, 300000],
          borderColor: '#10b981', // Tailwind green-500 hayop na yan need iconfigure pa
          backgroundColor: '#10b981',
          fill: false,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: false } }
      }
    });

    // Reservations chart (bar)
    new Chart(document.getElementById('reservationChart'), {
      type: 'bar',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [
          { label: 'Booked', data: [40, 50, 60, 30, 70, 80, 55], backgroundColor: '#10b981' },
          { label: 'Canceled', data: [5, 10, 8, 3, 12, 7, 6], backgroundColor: '#ef4444' } // Tailwind red-500 fckthishsit 
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: { stacked: true },
          y: { stacked: true, beginAtZero: true }
        }
      }
    });

    // Reservations chart (This Month - bar)
    new Chart(document.getElementById('monthChart'), {
      type: 'bar',
      data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [
          { label: 'Booked', data: [120, 150, 180, 140], backgroundColor: '#3b82f6' },
          { label: 'Canceled', data: [15, 20, 40, 12], backgroundColor: '#f59e0b' }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    // Booking by platform chart (doughnut)
    new Chart(document.getElementById('platformChart'), {
      type: 'doughnut',
      data: {
        labels: ['Direct', 'Booking.com', 'Agoda', 'Airbnb', 'Hotels.com', 'Others'],
        datasets: [{
          data: [61, 12, 11, 9, 5, 2],
          backgroundColor: [
            '#10b981', // Direct - green
            '#3b82f6', // Booking.com - blue
            '#f59e0b', // Agoda - amber
            '#8b5cf6', // Airbnb - purple
            '#ef4444', // Hotels.com - red
            '#6b7280'  // Others - gray
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
          padding: {
            top: -40,
            bottom: 50,
            left: 0,
            right: 5
          }
        },
        plugins: {
          legend: {
            position: 'right',
            align: 'center',
            labels: {
              font: { size: 15 },
              color: '#374151',
              padding: 50,
              generateLabels: function (chart) {
                const data = chart.data;
                if (data.labels.length && data.datasets.length) {
                  return data.labels.map((label, i) => {
                    const value = data.datasets[0].data[i];
                    const backgroundColor = data.datasets[0].backgroundColor[i];
                    return {
                      text: `${label} (${value})`,
                      fillStyle: backgroundColor,
                      strokeStyle: backgroundColor,
                      lineWidth: 2,
                      hidden: isNaN(value),
                      index: i
                    };
                  });
                }
                return [];
              }
            }
          }
        }
      }
    });

    // Sample data lang 
    const roomTypesData = {
      labels: ["Standard", "Deluxe", "Suite"],
      datasets: [{
        data: [83, 62, 36], // example counts
        backgroundColor: [
          "rgba(59, 130, 246, 0.7)",  // Standard
          "rgba(16, 185, 129, 0.7)",  // Deluxe
          "rgba(245, 158, 11, 0.7)"   // Suite
        ],
        borderColor: [
          "rgba(59, 130, 246, 1)",
          "rgba(16, 185, 129, 1)",
          "rgba(245, 158, 11, 1)"
        ],
        borderWidth: 1
      }]
    };

    const config = {
      type: "bar",
      data: roomTypesData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return `${context.label}: ${context.formattedValue}`;
              }
            }
          }
        },
        scales: {
          x: {
            categoryPercentage: 0.6,
            barPercentage: 0.8
          },
          y: {
            title: {
              display: true,
              text: "No. Of Bookings"
            },
            beginAtZero: true
          }
        }
      }
    };

    const ctx = document.getElementById('roomTypesChart').getContext('2d');
    const roomTypesChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Standard', 'Deluxe', 'Suite'],
        datasets: [{
          label: 'Bookings',
          data: [1200, 1500, 800],
          backgroundColor: [
            'rgba(59, 130, 246, 1)',   // Standard - Blue
            'rgba(16, 185, 129, 1)',   // Deluxe - Green
            'rgba(234, 88, 12, 1)'     // Suite - Orange
          ],
          borderColor: [
            'rgba(59, 130, 246, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(234, 88, 12, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        layout: {
          padding: {
            top: 10,
            bottom: 20,
            left: 10,
            right: 10
          }
        },
        scales: {
          x: {
            ticks: {
              padding: 5
            }
          }
        },
        responsive: true,
        maintainAspectRatio: false
      }
    });
  </script>
  <script src="/js/admin/reservation.js"></script>
  <script src="/js/admin/calendar.js"></script>
</body>

</html>