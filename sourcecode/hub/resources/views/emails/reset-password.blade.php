<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ trans('messages.reset-password') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            color: #555555;
            font-size: 16px;
            margin-bottom: 10px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>{{ trans('messages.reset-password') }}</h1>
    <p>{{ trans('messages.reset-password-email-message') }}</p>
    <p>{{ trans('messages.reset-password-email-action') }} <a href="{{ $resetLink }}">{{ trans('messages.reset-password') }}</a></p>
    <p>{{ trans('messages.reset-password-email-note') }}</p>
    <p>{{ trans('messages.reset-password-email-ignore') }}</p>
    <p>{{ trans('messages.reset-password-email-thanks', ['site' => config('app.name')]) }}</p>
</div>
<div class="footer">
    <p>{{ config('app.name') }}</p>
</div>
</body>
</html>
