<?php
return [
	"openai_api_key" => "", // Open api key
	"telegram_bot_token" => "", // Telegram bot token
	"telegram_chat_id" => "", // ID of your chat or channel
	"rss_url" => "https://www.wired.com/feed/rss", // Link to page with rss, replace with your own
	"language" => "Українська", // Language for translation and summarization
	"sent_articles_file" => "sent_articles.txt", // File to store sent articles
	"email_recipient" => "", // Email address to send notifications
	"email_template" => "email_template.php", // Template for sending admin emails
	
	// The language in the message depends on the language of these requests.
	"openai_title_prompt" => "Ти перекладач, який точно і природно перекладає текст на українську.",
	"openai_summary_prompt" => "Ти журналіст, який стисло переказує статті українською мовою.",
	"openai_summary_011" => "Переклади українською:",
	"openai_summary_012" => "Стисло перекажи текст українською мовою:",

	// Telegram messages
	"telegram_message_template" => "<b>{title}</b>\n\n{summary}\n\n<a href='{link}'>Read full article</a>",

	"telegram_success_message" => "✅ The latest article has been successfully sent to Telegram!",
	"telegram_already_sent" => "⚠️ The article has already been sent earlier."
];

