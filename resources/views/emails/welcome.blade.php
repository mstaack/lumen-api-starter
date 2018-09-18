<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
</head>
<body>
    <h2>
        {{ trans('messages.welcome_header', ['website' => config('constants.website_name'), 'name' => $name]) }}.
    </h2>
    <br/>
    {{ trans('messages.welcome_text') }}
    <br/><br/>
    <a href="{{ config('constants.frontend_url') . '/#/verification?token='. $token }}">{{ trans('messages.welcome_link') }}</a>
</body>
</html>
