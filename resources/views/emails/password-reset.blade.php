<!doctype html>
<html lang="{{ env('APP_LOCALE') }}">
<head>
</head>
<body>
    {{ trans('messages.password_reset_text', ['website' => config('constants.website_name')]) }}
    <br/>
    <a href="{{ config('constants.frontend_url') . '/#/password/recover?token='. $token }}">{{ trans('messages.password_reset_link') }}</a>
</body>
</html>
