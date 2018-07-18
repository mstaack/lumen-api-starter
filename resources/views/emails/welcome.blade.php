<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome</title>
</head>
<body>
<h2>Welcome to the site {{$name}},</h2>
<br/>
please click on the below link to verify your account.
<br/>
<a href="{{ url('auth/verify', ['token' => $token]) }}">Click here to verify your account.</a>
</body>
</html>