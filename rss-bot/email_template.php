<!-- email_template.php -->
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Error Notification</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			line-height: 1.6;
			color: #333;
		}
		.container {
			max-width: 600px;
			margin: 0 auto;
			padding: 20px;
			border: 1px solid #ddd;
			border-radius: 8px;
			background-color: #f9f9f9;
		}
		h1 {
			color: #e74c3c;
		}
		p {
			margin: 10px 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>⚠️ Error in Telegram Bot</h1>
		<p><strong>Error Description:</strong></p>
		<p><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
		<p><strong>Date and Time:</strong> <?= date("Y-m-d H:i:s"); ?></p>
	</div>
</body>
</html>
