// Default values
let nights = 1;
let guests = 1;
let roomType = 'single';

// Helper: calculate nights between two dates
function getNights(checkIn, checkOut) {
    const d1 = new Date(checkIn);
    const d2 = new Date(checkOut);

    const utc1 = Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate());
    const utc2 = Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate());

    const diffMs = utc2 - utc1;
    return Math.max(1, diffMs / (1000 * 60 * 60 * 24)); // ensure at least 1 night
}

// Core pricing calculation function
function calculateTotal({ roomType, nights, guests }) {
    const selectedRoom = document.querySelector(`input[name="room"][value="${roomType}"]`);
    if (!selectedRoom) return null;

    const basePrice = Number(selectedRoom.dataset.basePrice);
    let roomCost = basePrice * nights;

    let nightDiscount = nights > 2 ? roomCost * 0.15 : 0;

    let guestCharge = 0;
    if (roomType === 'single') guestCharge = basePrice * 0.10 * Math.max(0, guests - 1) * nights;
    else if (roomType === 'double') guestCharge = basePrice * 0.10 * Math.max(0, guests - 2) * nights;

    const subtotal = roomCost - nightDiscount + guestCharge;

    const total = (subtotal * 0.12) + subtotal;

    return { basePrice, roomCost, nightDiscount, guestCharge, subtotal, total };
}

// Update main UI
function updatePriceUI({ nights, guests, roomType }) {
    const pricing = calculateTotal({ nights, guests, roomType });
    if (!pricing) return;

    document.getElementById("room-price").textContent =
        `₱${pricing.basePrice.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
    document.getElementById("subtotal-price").textContent =
        `₱${pricing.subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
}

function handleUpdate() {
    const adults = Number(document.getElementById("adults").value) || 1;
    const children = Number(document.getElementById("children").value) || 0;
    const guests = adults + children;

    const selectedRoom = document.querySelector('input[name="room"]:checked');
    const roomType = selectedRoom ? selectedRoom.value : 'single';

    const daterangeInput = document.getElementById("daterange");
    const dates = daterangeInput.value.split(' to ');
    const checkin = dates[0] || '';
    const checkout = dates[1] || '';
    const nights = (checkin && checkout) ? getNights(checkin.replace(/\//g, '-'), checkout.replace(/\//g, '-')) : 1;

    updatePriceUI({ nights, guests, roomType });
}

// Listeners
document.querySelectorAll('input[name="room"]').forEach(radio => radio.addEventListener('change', handleUpdate));
document.getElementById('guests').addEventListener('input', handleUpdate);
document.addEventListener("DOMContentLoaded", handleUpdate);