flatpickr("#birthDate", {
    dateFormat: "Y-m-d",
    maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)),
    altInput: true,
    altFormat: "F j, Y",
});