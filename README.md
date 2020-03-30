# Laravel SendGrid Example

## Laravel Versions

### Laravel 7
For Laravel 7, install v3 of `s-ichikawa/laravel-sendgrid-driver`.

### Laravel 6
For Laravel 6, install v2. Also you use `MAIL_DRIVER` instead of of `MAIL_MAILER`.

## Etc

### Command

`php artisan io:email-test --to=<address>`

### Env vars

```dotenv
SENDGRID_API_KEY=
SENDGRID_UNSUBSCRIBE_GROUP_ID=
SENDGRID_TEMPLATE_ID=
```

### Template

The template file used is:
`app/Console/Commands/EmailSendTestCommand/template.html`.

This file is not directly used in this project; rather it is (to be) kept in sync with the template used within the SendGrid account and referenced by template ID.
