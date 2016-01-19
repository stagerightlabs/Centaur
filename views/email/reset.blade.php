<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>Reset Your Password</h2>

		<p>To change your password, <a href="{{ route('auth.password.reset.form', urlencode($code)) }}">click here.</a></p>
		<p>Or point your browser to this address: <br /> {!! route('auth.password.reset.form', urlencode($code)) !!} </p>
		<p>Thank you!</p>
	</body>
</html>