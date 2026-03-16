document.addEventListener("DOMContentLoaded", () => {
  // Select main wrapper
  const wrapper = document.querySelector('.wrapper');

  // Select links
  const loginLink = document.querySelectorAll('.login-link'); 
  const signupLink = document.querySelector('.signup-link');
  const forgotLink = document.querySelector('.forgot-link');

  // Select buttons
  const btnPopup = document.querySelector('.btnLogin-popup');

  // Switch to Signup 
  signupLink.addEventListener('click', () => {
    wrapper.classList.add('active');
  });

  // Switch back to Login
  loginLink.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      wrapper.classList.remove('active');
      wrapper.classList.remove('active-forgot');
    });
  });

  // Open Popup
  btnPopup.addEventListener('click', () => {
    wrapper.classList.add('active-popup');
  });

  // Close Popup (fix: handle all close icons)
  const closeIcons = document.querySelectorAll('.icon-close');
  closeIcons.forEach(icon => {
    icon.addEventListener('click', () => {
      wrapper.classList.remove('active-popup');
      wrapper.classList.remove('active');
      wrapper.classList.remove('active-forgot');
    });
  });

  // Switch to Forgot Password 
  forgotLink.addEventListener('click', (e) => {
    e.preventDefault();
    wrapper.classList.add('active-forgot');
  });

  // Forgot Password Validation 
  const forgotForm = document.getElementById('forgotForm');
  const newPassword = document.getElementById('newPassword');
  const confirmPassword = document.getElementById('confirmPassword');

  forgotForm.addEventListener('submit', (e) => {
    e.preventDefault();

    if (newPassword.value !== confirmPassword.value) {
      alert('New password and Confirm password do not match!');
      return;
    }

    alert('Password reset successful!');
  });

  // Date of Birth dropdowns
  const daySelect = document.getElementById("dob-day");
  for (let d = 1; d <= 31; d++) {
    let option = document.createElement("option");
    option.value = d;
    option.textContent = d;
    daySelect.appendChild(option);
  }

  const monthSelect = document.getElementById("dob-month");
  const months = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
  ];
  months.forEach((m, i) => {
    let option = document.createElement("option");
    option.value = i + 1;
    option.textContent = m;
    monthSelect.appendChild(option);
  });

  const yearSelect = document.getElementById("dob-year");
  for (let y = 1960; y <= 2026; y++) {
    let option = document.createElement("option");
    option.value = y;
    option.textContent = y;
    yearSelect.appendChild(option);
  }
});
