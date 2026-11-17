<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Email</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h1>You have received a contact email</h1>
    <p><strong>Name:</strong> {{ $mailData['name'] }}</p>
    <p><strong>Email:</strong> {{ $mailData['email'] }}</p>
    <p><strong>Subject:</strong> {{ $mailData['subject'] }}</p>
    <p><strong>Message:</strong></p>
    <p>{{ $mailData['message'] }}</p>
</body>
</html>
