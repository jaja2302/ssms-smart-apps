<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Taksasi Mobile Pro</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/CBI-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>
    html,
    body {
        max-width: 100%;
        overflow-x: hidden;
        background-color: white;
    }
</style>

<body>
    <div style="margin-top: 3%">

        @yield('content')
    </div>
</body>

</html>