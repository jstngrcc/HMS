document.addEventListener("DOMContentLoaded", () => {
  const guestInput = document.getElementById("guests");
  const guestLabel = document.getElementById("guests-label");
  const guestAddon = document.getElementById("guests-addon");

  const roomType = document.body.dataset.roomType;

  const fullConfig = {
    standard: {
      single: { min: 1, max: 2, add: 1},
      double: { min: 1, max: 3, add: 2}
    },
    deluxe: {
      single: { min: 1, max: 4, add: 1 },
      double: { min: 1, max: 6, add: 2 }
    },
    suite: {
      single: { min: 1, max: 6, add: 1},
      double: { min: 1, max: 10, add: 2 }
    }
  };

  function updateGuestUI() {
    const selected = document.querySelector('input[name="room"]:checked');
    if (!selected) return; // safety

    const occupancy = selected.value;
    const { min, max, add } = fullConfig[roomType][occupancy];

    guestInput.min = min;
    guestInput.max = max;

    guestLabel.textContent = `Guests (Max ${max})`;
    guestAddon.textContent = `Additional guests (above ${add}) cost 10% of the room rate pernight.`;

    if (guestInput.value > max) guestInput.value = min;
  }

  document.querySelectorAll('input[name="room"]').forEach(radio => {
    radio.addEventListener("change", updateGuestUI);
  });

  updateGuestUI();
});