$(document).ready(function () {
    $('#clearFilters').click(function () {
        // Clear text & number inputs
        $('#daterange, #adults, #children, #beds').val('');

        // Uncheck radios
        $('input[name="room"]').prop('checked', false);

        // Reset select dropdown
        $('select[name="room_type"]').prop('selectedIndex', 0);
    });
});

//Server side
{/* <script>
$(document).ready(function () {
    $('#clearFilters').click(function () {

        $.ajax({
            url: '/clear-filters.php', // create this file
            type: 'POST',
            success: function () {
                // Clear UI after server reset
                $('#daterange, #adults, #children, #beds').val('');
                $('input[name="room"]').prop('checked', false);
                $('select[name="room_type"]').prop('selectedIndex', 0);
            }
        });

    });
});
</script> */}