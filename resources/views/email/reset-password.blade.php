<!DOCTYPE html>
<html>
<head>
    <title> Reset Password</title>
</head>
<body>
    <h1>Hello, {{ $mailData['user']->name }}</h1>
    <p>Please click the link below to reset your password:</p>
    <a href="{{ route('front.resetPassword', $mailData['token']) }}">Click Here to Reset Password</a>
</body>
</html>
