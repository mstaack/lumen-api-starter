<!doctype html>
<html lang="en">
<head>
    <title>Password Reset</title>
</head>
<body>
You have requested to change your password. Use the link below to set a new password.<br/>
<a href="{{ url('auth/password/recover', ['token' => $token]) }}">POST to this link to set a new password (Usually also via frontend page with forms)</a>
</body>
</html>