<div class="panel">
    <h3>Email Export Report</h3>
    <form method="post" action="{$export_url}">
        <div class="form-group">
            <label for="date_from">From Date</label>
            <input type="date" name="date_from" id="date_from" class="form-control native-date" required />
        </div>
        <div class="form-group">
            <label for="date_to">To Date</label>
            <input type="date" name="date_to" id="date_to" class="form-control native-date" required />

        </div>
        <button type="submit" name="submitExport" class="btn btn-primary">Export Emails</button>
    </form>
</div>

{literal}
    <script>
        // document.querySelectorAll('.native-date').forEach(function(input) {
        //     input.addEventListener('click', function() {
        //         this.showPicker && this.showPicker(); // Chrome-supported API
        //     });
        // });
    </script>
{/literal}
