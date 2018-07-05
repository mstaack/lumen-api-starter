<!DOCTYPE html>
<html>
<head>
    <title>Please verify your account</title>
</head>

<body>
<h2>Welcome to the site {{$user['name']}}</h2>
<br/>
Please click on the below link to verify your account
<br/>
<a href="{{ url('auth/verify/' . $user->verifyUser->token) }}">Click here to verify your account.</a>
</body>

</html>
