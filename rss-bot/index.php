<?php

$config = include 'config.php';

$openai_api_key = $config["openai_api_key"];
$telegram_bot_token = $config["telegram_bot_token"];
$telegram_chat_id = $config["telegram_chat_id"];
$rss_url = $config["rss_url"];
$language = $config["language"];
$sent_articles_file = $config["sent_articles_file"];
$email_recipient = $config["email_recipient"];
$email_subject = $config["email_subject"];
$email_template = $config["email_template"];

// 1. Function to send email using a template
function send_email_with_template($to, $subject, $email_template, $data) {
	if (!file_exists($email_template)) {
		echo "❌ Email template not found: $email_template";
		return false;
	}

	extract($data);
	ob_start();
	include $email_template;
	$body = ob_get_clean();

	if (empty($subject)) {
		$subject = "Message from Telegram Bot"; // Default subject
	}

	$headers = "From: $email_recipient\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	return mail($to, $subject, $body, $headers);
}

// 2. Retrieve the latest article from RSS
function get_latest_article($rss_url) {
	$rss_feed = simplexml_load_file($rss_url);
	if (!$rss_feed) {
		global $email_recipient, $email_subject, $email_template;
		send_email_with_template($email_recipient, $email_subject, $email_template, [
			"error_message" => "❌ RSS loading error. URL: $rss_url"
		]);
		die("❌ RSS loading error");
	}

	$latest_item = $rss_feed->channel->item[0];
	return [
		"title" => (string)$latest_item->title,
		"link" => (string)$latest_item->link,
		"description" => strip_tags((string)$latest_item->description)
	];
}

// 3. Check if the article has already been sent
function is_article_sent($article_link, $file) {
	if (!file_exists($file)) file_put_contents($file, "");
	$sent_articles = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	return in_array($article_link, $sent_articles);
}

// 4. Mark the article as sent
function mark_article_as_sent($article_link, $file) {
	file_put_contents($file, $article_link . PHP_EOL, FILE_APPEND);
}

// 5. Use OpenAI API for translation or summarization
function openai_translate_or_summarize($text, $prompt_template, $language, $openai_api_key) {
	if (empty($text)) {
		return "❌ Text is missing or empty.";
	}

	$api_url = "https://api.openai.com/v1/chat/completions";
	$headers = [
		"Content-Type: application/json",
		"Authorization: Bearer $openai_api_key"
	];

	$prompt = str_replace("{language}", $language, $prompt_template);

	// Logging
	file_put_contents('debug.log', "Prompt: $prompt\nText: $text\n", FILE_APPEND);

	$data = [
		"model" => "gpt-3.5-turbo",
		"messages" => [
			["role" => "system", "content" => $prompt],
			["role" => "user", "content" => $text]
		],
		"max_tokens" => 200
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		$error_msg = curl_error($ch);
		global $email_recipient, $email_subject, $email_template;
		send_email_with_template($email_recipient, $email_subject, $email_template, [
			"error_message" => "❌ OpenAI request error: $error_msg"
		]);
		curl_close($ch);
		exit;
	}
	curl_close($ch);

	$response_data = json_decode($response, true);
	if (isset($response_data['error'])) {
		$error_message = $response_data['error']['message'];
		global $email_recipient, $email_subject, $email_template;
		send_email_with_template($email_recipient, $email_subject, $email_template, [
			"error_message" => "❌ OpenAI API Error: $error_message"
		]);
		exit;
	}

	return $response_data['choices'][0]['message']['content'] ?? "❌ Unknown OpenAI error.";
}

// Call function
$translated_title = openai_translate_or_summarize($article["title"], $config["openai_title_prompt"], $language, $openai_api_key);
$summary = openai_translate_or_summarize($article["description"], $config["openai_summary_prompt"], $language, $openai_api_key);

// 6. Send message to Telegram
function send_to_telegram($message, $telegram_bot_token, $telegram_chat_id) {
	$url = "https://api.telegram.org/bot$telegram_bot_token/sendMessage";
	$post_fields = [
		'chat_id' => $telegram_chat_id,
		'text' => $message,
		'parse_mode' => 'HTML'
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	$response_data = json_decode($response, true);
	curl_close($ch);

	if (!$response_data["ok"]) {
		global $email_recipient, $email_subject, $email_template;
		send_email_with_template($email_recipient, $email_subject, $email_template, [
			"error_message" => "❌ Telegram sending error: " . $response_data["description"]
		]);
		exit;
	}

	return $response_data;
}

// 7. Main logic
$article = get_latest_article($rss_url);

if (is_article_sent($article["link"], $sent_articles_file)) {
	echo $config["telegram_already_sent"];
	exit;
}

$translated_title = openai_translate_or_summarize($article["title"], $config["openai_title_prompt"] ?? $config["openai_summary_011"], $language, $openai_api_key);
$summary = openai_translate_or_summarize($article["description"], $config["openai_summary_prompt"] ?? $config["openai_summary_012"], $language, $openai_api_key);

$message = str_replace(["{title}", "{summary}", "{link}"], [$translated_title, $summary, $article['link']], $config["telegram_message_template"]);

send_to_telegram($message, $telegram_bot_token, $telegram_chat_id);
mark_article_as_sent($article["link"], $sent_articles_file);

echo $config["telegram_success_message"];
