<!DOCTYPE html>
<html>

<head>
    <title>Database Pupuk</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
</head>

<body>

    <div class="container">
        @yield('content')
    </div>

</body>

</html>

<script>
    $(document).ready( function () {
    $('#myTable').DataTable();
} );
</script>