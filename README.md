# rss-bot

This script automatically:

- Fetches the latest article from an RSS feed.
- Translates or summarizes the article using the OpenAI API.
- Sends the translated title and summarized description to a Telegram channel.
- Saves information about sent articles to avoid duplicates.
- Sends an email to the administrator with a description of the issue in case of errors (RSS loading, OpenAI API, or Telegram).

---

What’s included in the archive

- **index.php** – The main script file. Handles the core logic (fetching the article, processing via OpenAI, and sending to Telegram).
- **config.php** – Configuration file for setting up API keys, Telegram parameters, RSS feed URL, translation language, etc.
- **email_template.php** – HTML email template for administrator notifications in case of errors.
- **sent_articles.txt** – File for storing links to already sent articles (automatically created if it doesn’t exist).
- **debug.log** – File for recording technical information (logs) about the script's operation.

---

How to install the script on a server

1. **Unpack the archive:** Upload the files to your server in the desired directory (e.g., /var/www/html/rss-bot/).

2. **Configure the script:** Open the config.php file and set:
   - API key for OpenAI: "openai_api_key".
   - Telegram Bot token: "telegram_bot_token".
   - ID of your Telegram channel or chat: "telegram_chat_id".
   - RSS feed URL: "rss_url".
   - Administrator’s email: "email_recipient".

3. **Check PHP compatibility:** Make sure your server has PHP (version 7.4 or higher) installed and can access external APIs (OpenAI, Telegram).

4. **Set up a cron job (automatic execution):** To ensure the script runs regularly.
