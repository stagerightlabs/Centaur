<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>Welcome</h2>

		<p><b>Account:</b> {{ $email }}</p>
		<p>To activate your account, <a href="{{ route('auth.activation.attempt', urlencode($code)) }}">click here.</a></p>
		<p>Or point your browser to this address: <br /> {!! route('auth.activation.attempt', urlencode($code)) !!} </p>
		<p>Thank you!</p>
	</body>
</html>