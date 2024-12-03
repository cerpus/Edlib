<!DOCTYPE html>
<html data-openapi-url="{{ route('ndla-legacy.openapi-schema') }}" lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Swagger</title>
    </head>

    <body>
        <div id="swagger"></div>
        @vite('resources/js/swagger.js')
    </body>
</html>
