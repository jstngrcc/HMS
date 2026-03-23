<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standard Rooms</title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/output.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>
    <?php require_once __DIR__ . '/../components/header.view.php'; ?>

    <div class="py-10 px-30 flex flex-col justify-center">
        <div class="justify-start text-black text-base font-normal font-crimson">Home > Book Now</div>

        <div class="flex">
            <div class="w-[734px] h-[671px] bg-white rounded shadow-[0px_0px_4px_0px_rgba(0,0,0,0.25)]">
                <div class="justify-center text-black text-3xl font-normal font-crimson">Standard Room</div>
                <div class="flex items-center justify-start">
                    <img src="/assets/icons/star.svg" alt="star" class="w-4 h-4">
                    <img src="/assets/icons/star.svg" alt="star" class="w-4 h-4">
                    <img src="/assets/icons/star.svg" alt="star" class="w-4 h-4">
                    <img src="/assets/icons/star.svg" alt="star" class="w-4 h-4">
                    <img src="/assets/icons/star.svg" alt="star" class="w-4 h-4">
                    <h6 class="font-semibold font-roboto"></h6>1 Review</h6>
                </div>

                <div class="flex">
                    <div class="flex-1">
                        <img class="w-40 h-28 rounded-[3px]" src="/assets/images/g3.jpg" />
                        <div class="w-40 h-28 bg-zinc-300 rounded-[3px]"></div>
                        <div class="w-40 h-28 bg-zinc-300 rounded-[3px]"></div>
                    </div>
                    <div class="flex-2">
                        <img class="w-[526px] h-[556px] rounded" src="/assets/images/standard.jpg" />
                    </div>
                </div>
            </div>
            <div>
                <div class="w-80 h-[515px] bg-white rounded shadow-[0px_0px_4px_0px_rgba(0,0,0,0.25)]">
                    <div class="justify-center text-black text-xl font-normal font-crimson">Check In - Check Out</div>
                    <input type="text" name="checkin" id="dateRange" placeholder="Check-In — Check-Out"
                        class="bg-white rounded-sm p-2 text-crimson-600 font-crimson border border-gray-300">
                    <div class="justify-center text-black text-xl font-normal font-crimson">Guests</div>
                    <input type="number" placeholder="Guests" class="bg-white rounded-sm p-2 text-crimson-600 font-crimson border border-gray-300">
                    <div class="justify-center text-black text-xl font-normal font-crimson">Room Type</div>
                    <input type="radio">
                    <label for="html">Single</label>
                    <input type="radio">
                    <label for="html">Double</label>
                    <div class="justify-center text-zinc-500 text-xl font-normal font-crimson">Room Price</div>
                    <div class="justify-center text-zinc-500 text-xl font-normal font-crimson">Total</div>
                    <button
                        class="text-white font-roboto text-[16px] font-semibold leading-normal rounded-sm bg-[#C39C4D] p-3">
                        BOOK NOW
                    </button>
                </div>
                <div class="w-80 h-36 bg-white rounded shadow-[0px_0px_4px_0px_rgba(0,0,0,0.25)]">
                    <p>Additional</p>
                </div>
            </div>
        </div>
    </div>
    <script src="/js/daterange.js"></script>
</body>

</html>