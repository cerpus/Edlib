<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Returning to Edlib</title>
    </head>

    <body>
        <form action="{{ $request->getUrl() }}" method="{{ $request->getMethod() }}" name="lti_return">
            {!! $request->toHtmlFormInputs() !!}
        </form>
        <script>
            document.forms.lti_return.submit();
        </script>
    </body>
</html>
