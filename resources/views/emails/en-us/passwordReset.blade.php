<!DOCTYPE html>
<html>
<body>
You have requested to change your password. Use the link below to set a new password.<br/>
<a href="{{ url('auth/password/reset/' . $token) }}">POST to this link to set a new password.</a>
</body>

</html>
