<script>
    document.getElementById('clear_filter').onclick = function() {
        document.getElementById("search").value = "";
        document.getElementById('filter_form').submit();
    };
    $(document).ready(function() {
        $("#select_all").click( function(){
            $('.checkbox-item').prop('checked', this.checked);
        });
        $('#delete_selected').click(function(){
            var count = 0;
            $('input[name="ids[]"]:checked').each(function() {
                count++;
            });
            if (!count)
            {
                alert('Please select the record before deleting!')
                return false;
            }
            var result = confirm("You are going to delete " + count + " record(s). Are you sure ?");
            if (result) {
                $('#formList').submit();
            }
            else
            {
                return false;
            }
        });
        $('#sort').on("change", function (e) {
            $('#filter_form').submit()
        });
        $('#limit').on("change", function (e) {
            $('#filter_form').submit()
        });
    });
</script>