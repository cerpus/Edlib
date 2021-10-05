<!DOCTYPE html>
<html>
<head>
    <title>Not found.</title>

    <link href="//fonts.googleapis.com/css?family=Lato:100,200" rel="stylesheet" type="text/css">

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #8c9aa1;
            display: table;
            font-weight: 200;
            font-family: 'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 72px;
            margin-bottom: 40px;
        }

        .request-id  {
            color: #616b72;
            font-weight: 200;
            font-size: 32px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title">Not found</div>
        <div class="request-id">ID: {{ app('requestId') ?? 'undefined' }}</div>
        @if(config("app.debug", false) === true)
            <div class="errormessage exception">{{ $exception->getMessage() }}</div>
        @endif
    </div>
</div>
</body>
</html>
