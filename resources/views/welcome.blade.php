<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    @vite('resources/css/app.css')
    <title>Catálogo Agua Marina - Aguamarina Colombia</title>
    <link rel="icon" type="image/png" href="{{ asset('/storage/logo.png') }}">
</head>

<body>
    <div id="app"></div>
    @vite('resources/js/app.js')
</body>

</html>
