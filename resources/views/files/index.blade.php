<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index of /{{ $directory }}</title>
</head>
<body>
    <h1>Index of /{{ $directory }}</h1>
    <hr>
    <pre><a href="{{ route('dashboard') }}">../</a>
@foreach ($files as $filename)<a href="{{ route('whitelist-files.show', [$directory, $filename]) }}">{{ $filename }}</a>
@endforeach</pre>
    <hr>
</body>
</html>