document.addEventListener("DOMContentLoaded", () => {
  const roomsContainer = document.getElementById("rooms-container");
  const summary = document.getElementById("summary");
  const toggleRoomsBtn = document.getElementById("toggle-rooms");
  const roomsWrapper = document.querySelector(".rooms-wrapper");
  const iconClose = document.querySelector(".rooms-wrapper .icon-close");
  const doneBtn = document.getElementById("done-btn");

  let roomCount = 1;

  // Utility: populate a select with numeric options
  function populateSelect(select, min, max) {
    for (let i = min; i <= max; i++) {
      const option = document.createElement("option");
      option.value = i;
      option.textContent = i;
      select.appendChild(option);
    }
  }

  // Update summary text
  function updateSummary() {
    const rooms = roomsContainer.querySelectorAll(".room-select");
    let adults = 0, children = 0;
    rooms.forEach(room => {
      adults += +room.querySelector(".adults").value;
      children += +room.querySelector(".children").value;
    });
    summary.textContent = `${adults} Adults, ${children} Children, ${rooms.length} Rooms`;
  }

  // Create a new room row (without Done button!)
  function createRoomRow(number) {
    const div = document.createElement("div");
    div.className = "room-select";
    div.innerHTML = `
      <span>Room ${number}:</span>
      <ion-icon name="person"></ion-icon>Adults:
      <select class="adults"></select>
      <ion-icon name="people"></ion-icon>Children:
      <select class="children"></select>
      <button class="remove-room">Remove</button>
      <button class="add-room-inline">+ Add Room</button>
    `;

    populateSelect(div.querySelector(".adults"), 0, 10);
    populateSelect(div.querySelector(".children"), 0, 10);

    return div;
  }

  // Initialize first room
  const firstRoom = roomsContainer.querySelector(".room-select");
  populateSelect(firstRoom.querySelector(".adults"), 0, 10);
  populateSelect(firstRoom.querySelector(".children"), 0, 10);

  // Event delegation for inline buttons and selects
  roomsContainer.addEventListener("click", e => {
    const target = e.target;

    if (target.classList.contains("remove-room")) {
      const room = target.closest(".room-select");
      room.remove();
      // Ensure at least one room exists
      if (roomsContainer.querySelectorAll(".room-select").length === 0) {
        roomCount = 1;
        roomsContainer.appendChild(createRoomRow(roomCount));
      }
      updateSummary();
    }

    if (target.classList.contains("add-room-inline")) {
      roomCount++;
      const newRoom = createRoomRow(roomCount);
      roomsContainer.appendChild(newRoom);
      updateSummary();
    }
  });

  roomsContainer.addEventListener("change", e => {
    if (e.target.matches(".adults, .children")) updateSummary();
  });

  // Done button (single, below summary)
  doneBtn.addEventListener("click", () => {
    alert(summary.textContent);
    roomsWrapper.classList.remove("active-popup");
  });

  // Toggle popup
  toggleRoomsBtn.addEventListener("click", () => {
    roomsWrapper.classList.add("active-popup");
  });

  // Close popup
  iconClose.addEventListener("click", () => {
    roomsWrapper.classList.remove("active-popup");
  });

  updateSummary();
});
