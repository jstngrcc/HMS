document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action="/cart-submit"]');
    const modal = document.getElementById('booking-modal');
    const closeModalBtn = document.getElementById('close-modal');
    const continueBtn = document.getElementById('continue-browsing');
    const proceedBtn = document.getElementById('proceed-checkout');

    const adultsInput = document.getElementById('adults');
    const childrenInput = document.getElementById('children');
    const roomRadios = document.querySelectorAll('input[name="room"]');

    function getNights(checkIn, checkOut) {
        const d1 = new Date(checkIn);
        const d2 = new Date(checkOut);
        const utc1 = Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate());
        const utc2 = Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate());
        const diffMs = utc2 - utc1;
        return Math.max(1, diffMs / (1000 * 60 * 60 * 24));
    }

    function calculateTotal({ roomType, nights, adults, children }) {
        const selectedRoom = document.querySelector(`input[name="room"][value="${roomType}"]`);
        if (!selectedRoom) return null;

        const basePrice = Number(selectedRoom.dataset.basePrice);
        const totalGuests = adults + children;

        let roomCost = basePrice * nights;
        let nightDiscount = nights > 2 ? roomCost * 0.15 : 0;
        let guestCharge = basePrice * 0.10 * Math.max(0, totalGuests - 1) * nights;

        const subtotal = roomCost - nightDiscount + guestCharge;
        const total = subtotal * 1.12; // 12% tax

        return { basePrice, roomCost, nightDiscount, guestCharge, subtotal, total };
    }

    function updateGuestMax() {
        const selectedRoom = document.querySelector('input[name="room"]:checked');
        if (!selectedRoom) return;
        const maxGuests = Number(selectedRoom.dataset.maxGuests);

        const adults = Number(adultsInput.value);
        const children = Number(childrenInput.value);

        adultsInput.max = maxGuests - children;
        childrenInput.max = maxGuests - adults;

        if (adultsInput.value > adultsInput.max) adultsInput.value = adultsInput.max;
        if (childrenInput.value > childrenInput.max) childrenInput.value = childrenInput.max;
    }

    function updateModalAndPricing() {
        const roomTypeEl = document.querySelector('input[name="room"]:checked');
        if (!roomTypeEl) return;

        const roomType = roomTypeEl.value;
        const checkinCheckout = document.getElementById('daterange').value;
        const dates = checkinCheckout.split(' to ');
        const checkinDate = dates[0]?.replace(/\//g, '-') || '-';
        const checkoutDate = dates[1]?.replace(/\//g, '-') || '-';
        const nights = getNights(checkinDate, checkoutDate);

        const adults = Number(adultsInput.value);
        const children = Number(childrenInput.value);

        const pricing = calculateTotal({ roomType, nights, adults, children });
        if (!pricing) return;

        document.getElementById('modal-guests').textContent =
            `${adults + children} ${adults + children > 1 ? 'Guests' : 'Guest'}`;
        document.getElementById('modal-room').textContent =
            roomType.charAt(0).toUpperCase() + roomType.slice(1);

        document.getElementById('modal-room-cost').textContent =
            `₱${pricing.roomCost.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        document.getElementById('modal-night-discount').textContent =
            `₱${pricing.nightDiscount.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        document.getElementById('modal-guest-charge').textContent =
            `₱${pricing.guestCharge.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        document.getElementById('modal-total').textContent =
            `₱${pricing.total.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
    }

    adultsInput.addEventListener('input', () => { updateGuestMax(); updateModalAndPricing(); });
    childrenInput.addEventListener('input', () => { updateGuestMax(); updateModalAndPricing(); });
    roomRadios.forEach(r => r.addEventListener('change', () => {
        adultsInput.value = 1;
        childrenInput.value = 0;
        updateGuestMax();
        updateModalAndPricing();
    }));

    // Form submit
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const adults = Number(adultsInput.value);
        if (adults < 1) {
            showToast("At least 1 adult is required");
            return;
        }

        const formData = new FormData(form);
        fetch(form.action, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if (!response.success) { showToast(response.error); return; }
                modal.classList.remove('hidden'); modal.classList.add('flex');
                updateModalAndPricing();
            });
    });

    closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));
    continueBtn.addEventListener('click', () => modal.classList.add('hidden'));
    proceedBtn.addEventListener('click', () => window.location.href = '/cart');

    // Initialize
    updateGuestMax();
    updateModalAndPricing();
});