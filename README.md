# BankApplication — Full-Stack PHP + IBM i Demo

A modern MVC web application that invokes IBM i (System i) stored procedures to process banking transactions, demonstrating clean architecture, modern security practices, and seamless legacy system integration.

**Perfect for:** Conference talks, portfolio showcases, and enterprise modernization tips & tricks websites.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | HTML5 + CSS3 (no frameworks) |
| **Backend** | PHP 8.3 with MVC architecture |
| **Authentication** | JWT + refresh tokens (httpOnly cookies) |
| **Database** | IBM i (ODBC, stored procedures calling RPG programs) |
| **Security** | CSRF tokens, prepared statements, secure cookie attributes |

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Browser                              │
│                   (HTML5 + CSS3)                            │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTP/HTTPS
                         ▼
┌─────────────────────────────────────────────────────────────┐
│               Front Controller Router                        │
│                  (public/index.php)                         │
└────────────────────────┬────────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        ▼                ▼                ▼
   ┌─────────┐    ┌─────────┐    ┌─────────┐
   │  Login  │    │Transact │    │ History │
   │ Control │    │ Control │    │ Control │
   └────┬────┘    └────┬────┘    └────┬────┘
        │              │              │
        └──────┬───────┴──────┬───────┘
               │              │
        ┌──────▼──────┐  ┌───▼─────────┐
        │  Auth       │  │  Transaction│
        │ Middleware  │  │   Model     │
        └──────┬──────┘  └───┬─────────┘
               │             │
               └──────┬──────┘
                      │
        ┌─────────────▼──────────────┐
        │   PDO / ODBC Connection    │
        └─────────────┬──────────────┘
                      │
        ┌─────────────▼──────────────┐
        │  IBM i Stored Procedures   │
        │   (System i / ODBC)        │
        └─────────────┬──────────────┘
                      │
        ┌─────────────▼──────────────┐
        │    RPG Programs / DB2      │
        │   (Business Logic)         │
        └────────────────────────────┘
```

---

## Key Features

- ✅ **User Authentication** — JWT tokens stored in httpOnly, Secure, SameSite=Strict cookies
- ✅ **Token Refresh Flow** — Single-use refresh tokens with server-side revocation and rotation
- ✅ **Transaction Processing** — Deposit/withdrawal via IBM i stored procedures
- ✅ **Transaction History** — Formatted timestamps, color-coded transaction types (green=deposit, red=withdrawal)
- ✅ **CSRF Protection** — Token validation on all POST forms
- ✅ **Error Handling** — Centralized exception handling with structured logging
- ✅ **Clean MVC** — Proper separation of models, controllers, views
- ✅ **Prepared Statements** — SQL injection safe with PDO parameterized queries
- ✅ **Environment Config** — Dotenv for secrets and configuration

---

## Quick Start

### Prerequisites

- **PHP 8.3+** with PDO/ODBC extensions
- **Composer** (for dependencies: Firebase JWT, Dotenv)
- **IBM i system** with ODBC connectivity (DSN configured)
- **Database tables:**
  - `<your-library>.user_logins` (username, pword)
  - `<your-library>.BalanceTab` (transtype, amount, transtime)
  - `<your-library>.refresh_tokens` (token_hash, username, expires_at)
- **Stored procedure:** `<your-library>.ProcessTransaction(amount, type, balance_out, message_out)`

### Setup

1. **Clone/download the project:**
   ```bash
   cd c:\php-8.3.11\BankApplication
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment:**
   Create `config/.env`:
   ```
   DB_DSN=odbc:your_dsn_name
   DB_USER=your_user
   DB_PASSWORD=your_password
   DB_LIBRARY=your_library_name
   JWT_SECRET_KEY=your_secret_key_here
   JWT_ACCESS_TTL=900
   JWT_REFRESH_TTL=2592000
   ```

4. **Start the dev server:**
   ```powershell
   php -S localhost:8000 -t public public/index.php
   ```

5. **Open in browser:**
   ```
   http://localhost:8000
   ```

---

## Project Structure

```
BankApplication/
├── public/                      # Web root (document root)
│   ├── index.php               # Router / front-controller
│   ├── Login.html              # Login form
│   ├── NewUser.html            # User registration form
│   ├── scripts.js              # Client-side utilities
│   └── styles.css              # Global stylesheet
│
├── src/                        # Application source
│   ├── bootstrap.php           # Env loading, error handlers, logging
│   ├── authMiddleware.php      # JWT validation middleware
│   ├── Login_Process.php       # Login handler (JWT issuance)
│   ├── logout.php              # Logout handler (token cleanup)
│   │
│   ├── controllers/
│   │   ├── DashboardController.php      # Dashboard page
│   │   ├── TransactController.php       # Transaction processing
│   │   ├── HistoryController.php        # Transaction history
│   │   └── RefreshTokenController.php   # Token refresh endpoint
│   │
│   ├── views/                  # HTML templates (no business logic)
│   │   ├── dashboard.php
│   │   ├── transact.php
│   │   └── history.php
│   │
│   ├── models/
│   │   └── TransactionModel.php         # Data access layer
│   │
│   ├── lib/
│   │   └── refresh_token_store.php      # Token persistence
│   │
│   └── utils/
│       └── csrf.php                     # CSRF token generation/validation
│
├── config/
│   └── .env                    # Environment secrets (not in repo)
│
├── vendor/                     # Composer dependencies
├── composer.json
├── composer.lock
└── README.md                   # This file
```

---

## API Routes

| Method | Route | Auth | Purpose |
|--------|-------|------|---------|
| GET/POST | `/` | No | Redirect to login |
| GET/POST | `/login` | No | User authentication (form or API) |
| GET | `/dashboard` | JWT | Dashboard / home |
| GET/POST | `/transact` | JWT | Deposit/withdrawal form and processing |
| GET | `/history` | JWT | Transaction history view |
| POST | `/token/refresh` | Refresh cookie | Issue new access token |
| GET | `/logout` | JWT | Clear auth cookies and tokens |
| GET | `/Login.html` | No | Static login page |
| GET | `/NewUser.html` | No | Static registration page |

---

## Conference Talking Points

### 1. **"No JavaScript Framework Overhead"**
```
Traditional SPA (React, Vue, Angular): 100+ KB bundle size
BankApplication: 5 KB HTML + 20 KB CSS
→ Instant load, no build step, zero framework lock-in
```

### 2. **"True MVC Architecture"**
```
❌ Bad: Business logic in views, SQL in controllers
✅ Good: Clean separation — models handle data, 
           controllers orchestrate, views render
→ Easy to test, easy to refactor, easy to teach
```

### 3. **"Modern Security by Default"**
```
✓ CSRF tokens on all forms
✓ httpOnly + Secure + SameSite=Strict cookies
✓ JWT refresh token rotation (single-use)
✓ Prepared statements (no SQL injection)
✓ Password hashing (bcrypt via password_verify())
```

### 4. **"Seamless IBM i Integration"**
```
1. PHP app calls stored procedure via ODBC/PDO
2. Stored procedure invokes RPG business logic
3. Data returned to PHP, presented to browser
→ No APIs needed, pure database integration
→ Leverages existing RPG investments
```

### 5. **"Production Patterns on Day One"**
```
✓ Centralized error handling & logging
✓ Environment-based configuration (Dotenv)
✓ Middleware for cross-cutting concerns (auth)
✓ Dependency injection in models
✓ Structured logging for debugging
```

### 6. **"Scalable Beyond the Demo"**
```
This POC is NOT a toy:
→ Add a unit test framework (PHPUnit)
→ Add database migrations (e.g., Phinx)
→ Add an ORM (Doctrine) for complex queries
→ Add a full framework (Laravel, Symfony)
→ Stays compatible with all improvements
```

---

## Testing the Full Flow

### Manual Flow (Browser)
1. Open http://localhost:8000
2. Click "Login" → Login.html
3. Enter credentials → POST /login (JWT issued)
4. Redirected to /dashboard
5. Click "Deposit/Withdraw" → /transact
6. Enter amount, type → POST /transact (stored proc called)
7. Click "History" → /history (view transactions)
8. Click "Logout" → /logout (cookies cleared, tokens revoked)

### API Flow (PowerShell / cURL)
```powershell
# 1. Login
$response = Invoke-WebRequest -Uri 'http://localhost:8000/login' `
  -Method POST `
  -Body @{ user_name='testuser'; password='testpass' } `
  -SessionVariable sess

# 2. Transact (JWT automatically sent in cookie)
$response = Invoke-WebRequest -Uri 'http://localhost:8000/transact' `
  -Method POST `
  -Body @{ amount='100'; transactionType='deposit'; csrf_token='...' } `
  -WebSession $sess

# 3. Refresh token
$response = Invoke-WebRequest -Uri 'http://localhost:8000/token/refresh' `
  -Method POST `
  -WebSession $sess
```

---

## Deployment Notes

### HTTPS / TLS
- The app detects HTTPS and sets the `Secure` flag on cookies automatically
- For production, use a reverse proxy (nginx, Apache) with SSL termination
- Set `$_SERVER['HTTPS']` or `$_SERVER['SERVER_PORT']==443` in your deployment

### Database Permissions
- `user_logins` table needs SELECT on username/pword columns
- `BalanceTab` table needs SELECT on transtype/amount/transtime
- `refresh_tokens` table needs full CRUD (INSERT, SELECT, DELETE)
- Stored procedure `ProcessTransaction` needs EXECUTE permission

### Secrets Management
- Never commit `config/.env` to version control
- Use `.env.example` as a template (with dummy values)
- Load secrets from environment at deploy time (systemd, Docker, cloud provider)

### Logging
- App logs to `logs/app.log` (created on first error)
- Rotate logs via external tool or middleware as app scales
- Monitor logs for failed auth attempts, DB errors

---

## Resources

- **Firebase JWT:** https://github.com/firebase/php-jwt
- **Dotenv:** https://github.com/vlucas/phpdotenv
- **IBM i ODBC:** https://www.ibm.com/docs/en/i/latest
- **PHP PDO:** https://www.php.net/manual/en/book.pdo.php
- **OWASP Security Best Practices:** https://owasp.org/

---

## License

This demo is provided as-is for educational and presentation purposes. Use and modify freely.

---

**Questions?** This project is designed for conference talks and educational websites. Each line of code has a teaching purpose. Enjoy! 🚀
