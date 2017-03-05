# MrBill

MrBill is an expense tracking tool. To use it, text "hello" to +1 (650) 772-4067.

## Tech stack

* Docker
* PHP 7
* PHPUnit
* Redis
* Twilio
* Mailgun
* Slim
* 100% test coverage

## Functionality

* Users can use the tool without any account sign-up or login. Users communicate with the tool via text message and are
  identified by phone number.
* Users can record expenses by texting the expense amount + hashtags (categories) to the tool.
* Users can link their credit card and/or bank alerts to the app to have credit card and bank transactions automatically
  tracked, in real time.
* The tool will learn to categorize credit card transactions automatically by matching manually-entered expenses
  to expenses found on credit cards, and by proactively asking users about unknown purchases.
* The tool will provide users with a link to a web-based interactive report of their expenses.
* Users can use the report to edit expenses and categories and view various charts and graphs.
