flatpickr("#dateRange", {
    mode: "range",
    dateFormat: "d/m/Y",
    allowInput: true,
    minDate: "today",
    onChange: function (selectedDates, dateStr, instance) {
        if (selectedDates.length === 1) {
            instance.set("minDate", selectedDates[0]);
        }
    }
});