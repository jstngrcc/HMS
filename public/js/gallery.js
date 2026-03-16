document.addEventListener("DOMContentLoaded", () => {
  const grid = document.querySelector(".collage-grid");
  const images = Array.from(grid.querySelectorAll("img"));
  const leftArrow = document.querySelector(".arrow.left");
  const rightArrow = document.querySelector(".arrow.right");

  let currentPage = 0;
  const perPage = 6;

  function renderPage() {
    images.forEach((img, i) => {
      const start = currentPage * perPage;
      const end = start + perPage;
      img.style.display = (i >= start && i < end) ? "block" : "none";
    });
  }

  leftArrow.addEventListener("click", () => {
    if (currentPage > 0) {
      currentPage--;
      renderPage();
    }
  });

  rightArrow.addEventListener("click", () => {
    if ((currentPage + 1) * perPage < images.length) {
      currentPage++;
      renderPage();
    }
  });

  // Initialize first batch
  renderPage();

  // --- Popup logic ---
  images.forEach(img => {
    img.addEventListener("click", () => {
      const popup = document.createElement("div");
      popup.classList.add("image-popup");

      const popupImg = document.createElement("img");
      popupImg.src = img.src;
      popupImg.alt = img.alt;

      popup.appendChild(popupImg);
      document.body.appendChild(popup);

      // "show" class for transition
      requestAnimationFrame(() => {
        popup.classList.add("show");
      });

      // Close when clicking outside the image
      popup.addEventListener("click", (e) => {
        if (e.target === popup) {
          popup.remove();
        }
      });
    });
  });
});
