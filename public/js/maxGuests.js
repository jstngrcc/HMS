document.addEventListener("DOMContentLoaded", () => {
  const roomRadios = document.querySelectorAll('input[name="room"]');
  const adultsInput = document.getElementById('adults');
  const childrenInput = document.getElementById('children');
  const guestsLabel = document.getElementById('guests-label');
  const guestAddon = document.getElementById("guests-addon");

  const roomType = document.body.dataset.roomType;

  const fullConfig = {
    standard: { single: 2, double: 3 },
    deluxe: { single: 4, double: 6 },
    suite: { single: 6, double: 10 }
  };

  function updateMaxGuests() {
    const selected = document.querySelector('input[name="room"]:checked');
    if (!selected) return;

    const occupancy = selected.value;
    const maxGuests = fullConfig[roomType][occupancy];

    guestsLabel.textContent = `Guests (Max ${maxGuests})`;
    guestAddon.textContent = "Additional guests (above 1) cost 10% of the room rate per night.";

    const totalGuests = Number(adultsInput.value) + Number(childrenInput.value);

    // Adjust children if total exceeds max
    if (totalGuests > maxGuests) {
      childrenInput.value = Math.max(0, maxGuests - Number(adultsInput.value));
    }
  }

  adultsInput.addEventListener('input', () => {
    if (Number(adultsInput.value) < 1) adultsInput.value = 1; // Require at least 1 adult
    updateMaxGuests();
    handleUpdate(); // Update pricing
  });

  childrenInput.addEventListener('input', () => {
    if (Number(childrenInput.value) < 0) childrenInput.value = 0;
    updateMaxGuests();
    handleUpdate(); // Update pricing
  });

  roomRadios.forEach(radio => radio.addEventListener("change", updateMaxGuests));
  updateMaxGuests();
});