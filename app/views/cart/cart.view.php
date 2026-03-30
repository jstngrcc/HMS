<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/output.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body class="min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/../components/toast.view.php'; ?>
    <?php require_once __DIR__ . '/../components/header.view.php'; ?>


    <div class="flex-1 py-10 px-30 flex flex-col gap-5">
        <div class="justify-start text-black text-lg font-normal font-crimson">
            <a href="/home" class="hover:underline">Home</a> &gt; Cart
        </div>

        <h1 class="font-crimson font-bold text-3xl">YOUR BOOKING CART</h1>

        <div class="mx-auto h-1 w-full bg-yellow-900/60 rounded-lg"></div>

        <div id="cart-items-container" class="flex justify-center gap-5 font-roboto">
            <?php if (!empty($carts)): ?>
                <?php foreach ($carts as $cart): ?>
                    <div class="flex flex-col border rounded p-2 shadow w-full gap-2"
                        id="cart-item-<?php echo $cart['CartRoomID']; ?>">
                        <h2 class="font-bold"><?php echo htmlspecialchars($cart['RoomTypeName']); ?></h2>
                        <p>Room #: <?php echo htmlspecialchars($cart['RoomNumber']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($cart['BasePrice']); ?></p>
                        <p>Guests: <?php echo htmlspecialchars($cart['NumAdults']); ?></p>
                        <p>Check-in: <?php echo htmlspecialchars($cart['CheckInDate']); ?></p>
                        <p>Check-out: <?php echo htmlspecialchars($cart['CheckOutDate']); ?></p>

                        <button class="bg-red-600 text-white px-3 py-1 rounded mt-2 delete-cart-item"
                            data-cartroomid="<?php echo $cart['CartRoomID']; ?>">
                            Remove
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="flex flex-col border rounded p-2 shadow w-full gap-2">
                    <h1 class="font-bold">No booking found in cart</h1>
                    <p class="italic">You have not added any rooms or products to your cart yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php require_once __DIR__ . '/../components/footer.view.php'; ?>
    <script>
        $(document).ready(function () {
            $('.delete-cart-item').click(function () {
                const btn = $(this);
                const cartRoomID = btn.data('cartroomid');

                $.ajax({
                    url: '/cart-remove',
                    type: 'POST',
                    data: { cartRoomID: cartRoomID },
                    success: function (response) {
                        if (response.success) {
                            $('#cart-item-' + cartRoomID).remove();
                            if (response.cartCount > 0) {
                                $('#cart-count').text(response.cartCount).show();
                            } else {
                                $('#cart-count').hide();
                            }
                            showToast(response.message, 'success');
                            if ($('#cart-items-container').children('.flex[id^="cart-item-"]').length === 0) {
                                // Show "No booking found in cart" message
                                $('#cart-items-container').html(`
                                    <div class="flex flex-col border rounded p-2 shadow w-full gap-2" id="cart-empty-message">
                                        <h1 class="font-bold">No booking found in cart</h1>
                                        <p class="italic">You have not added any rooms or products to your cart yet.</p>
                                    </div>
                                `);
                            }
                        } else {
                            showToast(response.error);
                        }
                    },
                    error: function () {
                        showToast('An error occurred while removing the item.');
                    }
                });
            });
        });
    </script>
</body>

</html>