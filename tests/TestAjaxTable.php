<!-- Datatable CSS -->
<link href='//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>

<!-- jQuery Library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Datatable JS -->
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>

<!-- Table -->
<table id='TableRecordList' class='display dataTable'>

    <thead>
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Status</th>
    </tr>
    </thead>

</table>

<script>
    $(document).ready(function(){
        $('#TableRecordList').DataTable({
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url':'get_records.php'
            },
            'columns': [
                { data: 'id' },
                { data: 'email' },
                { data: 'first_name' },
                { data: 'last_name' },
                { data: 'status' },
            ]
        });
    });
</script>