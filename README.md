<p align="center" width="100%">
  <img width="800" height="275" alt="image" src="https://github.com/user-attachments/assets/50964b1c-2e80-4e71-a24a-ca313335a663" />
</p>


# Sneeze

**Sneeze** is a modern, token-based authentication starter kit for Laravel using Sanctum. Inspired by Breeze, it's designed for API-first apps that need clean, flexible auth — with no frontend assumptions and no reliance on cookies or CSRF.

Use it with any frontend: mobile, SPA, desktop, TUI — if it can send headers, it works.

---

## 💡 Why Laravel Sneeze?

Breeze is great for traditional Laravel apps — but it's tightly coupled to session cookies, CSRF protection, and frontend-specific flows like email verification links.

Sneeze takes a different approach:

- Clients only need to POST data — no clickable links or cookie handling required.
- No CSRF middleware, no session storage, no need to hit the `/csrf-token` endpoint.
- Auth uses **Bearer tokens**, not cookies — so your frontend can be on the **same domain or any other**.

You're not locked into a browser SPA. Whether you're building a mobile app, CLI, TUI, or remote dashboard, Sneeze is designed to get out of your way and let you build.

---

## 🔥 Features

- 🧪 API-first authentication using [Laravel Sanctum](https://laravel.com/docs/sanctum)
- 🔁 Register/Login issues a Sanctum token
- 🔐 8-digit **verification codes** for:
  - Email verification
  - Password resets
- 🧼 No CSRF. No session cookies. Just clean `Bearer` token auth
- 🧽 Automatic cleanup of expired or used verification codes
- 🧱 All key files (routes, controllers, traits, notifications) copied into your app so you can customize freely
- ✅ Implements Laravel’s `CanResetPassword` and `MustVerifyEmail` interfaces
- 🧪 Tested with both **PHPUnit** and **Pest**

---

## 🚀 Installation

**Laravel Sneeze is intended for new Laravel projects.**  
It scaffolds routes, controllers, traits, notifications, and test files directly into your app.

Install the package via Composer:

```bash
composer require boilingsoup/sneeze
```

Then run the install command:

```bash
php artisan sneeze:install         # Installs with PHPUnit tests
php artisan sneeze:install --pest  # Installs with Pest tests
```
The `sneeze:install` command will:

- Copy controllers, routes, notifications, traits, etc. into your `app/` and `routes/` directories

- Publish `config/sneeze.php` with all settings

---

## 🧬 Authentication Flow
All actions are done via API — no frontend coupling, no session redirects, no need for custom URLs.

| Action                             | Endpoint                                 | Method | Description                          |
|-----------------------------------|------------------------------------------|--------|--------------------------------------|
| Register                          | `/api/register`                          | POST   | Create a new user                    |
| Login                             | `/api/login`                             | POST   | Returns Sanctum token                |
| Logout                            | `/api/logout`                            | POST   | Revokes token                        |
| Request password reset            | `/api/forgot-password`                   | POST   | Sends 8-digit code                   |
| Reset password                    | `/api/reset-password`                    | POST   | Verifies code + sets new password    |
| Request email verification code   | `/api/email/verification-notification`   | POST   | Sends 8-digit code                   |
| Verify email                      | `/api/verify-email`                      | POST   | Verifies 8-digit code                |

---

## ⚙️ Configuration

Sneeze uses a simple config file to define expiration times for tokens and codes. These are set using [`CarbonInterval`](https://carbon.nesbot.com/docs/#api-carboninterval), which gives you expressive, readable control over durations.

File: `config/sneeze.php`

```php
use Carbon\CarbonInterval;

return [

    // Set how long Sanctum auth tokens are valid after login or registration
    'sanctum_auth_token_expiration' => CarbonInterval::months(1),

    // Set how long email verification codes are valid
    'email_verification_expiration' => CarbonInterval::minutes(15),

    // Set how long password reset codes are valid
    'password_reset_expiration' => CarbonInterval::minutes(15),

];
```

You can customize these values using any `CarbonInterval` expression.

Example: To make reset codes expire in 30 minutes, change:

```php
'password_reset_expiration' => CarbonInterval::minutes(30),
```
CarbonInterval supports durations like `minutes()`, `hours()`, `days()`, `weeks()`, `months()`, and more.
These intervals are applied **at the time the token or code is created**, ensuring consistent and timezone-aware expiration.

---

## ⏱ Scheduled Tasks
Sneeze registers two scheduled tasks in `routes/console.php` to keep your auth tables clean:

```php
// Prune expired Sanctum tokens (every minute)
Schedule::command('sanctum:prune-expired --hours=0')->everyMinute();

// Prune used or expired verification codes (every minute)
Schedule::command('sneeze:prune-stale')->everyMinute();
```

These tasks are added automatically when you install Sneeze.

- In development, you can run: `php artisan schedule:work`

- In production, set up a cron job to run `php artisan schedule:run` every minute

More info: [Laravel Scheduler Documentation](https://laravel.com/docs/scheduling#running-the-scheduler)

---

## 🔐 Security Notes
- Verification codes are generated using `random_int(10000000, 99999999)` — cryptographically secure.

- Codes are hashed before being stored in the database (like passwords.)

- Expired or used codes are automatically cleaned up via scheduled task.

- Token expiration is enforced (via Laravel Sanctum.)

- No CSRF needed — all clients authenticate via Bearer token header.

---

## 🧱 Customization
All important logic is published into your app for easy modification:

- `app/Http/Controllers/Auth/...`

- `app/Models/Traits/HasVerificationCodes.php`

- `app/Notifications/Auth/...`

---

## 📦 Credits
Created by [Boiling Soup](https://github.com/boilingsoup)

Inspired by Laravel Breeze
