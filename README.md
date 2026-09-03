# CalcApp

A small, responsive calculator built with PHP. It supports addition, subtraction, multiplication, and division, including validation for invalid input and division by zero.

The calculator is protected by a session-based login. Set `CALCAPP_USERNAME` and `CALCAPP_PASSWORD_HASH` in the hosting environment to replace the initial credentials. The password hash must be a lowercase SHA-256 value.

## Run locally

PHP 8.0 or newer is recommended.

```bash
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

