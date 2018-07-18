<!doctype html>
<html lang="en">
<head>
    <title>Please verify your account</title>
</head>
<body>
<h2>Welcome to the site {{$name}},</h2>
<br/>
please click on the below link to verify your account.
<br/>
<a href="{{ url('auth/verify', ['token' => $token]) }}">Click here to verify your account.</a>
</body>
</html>