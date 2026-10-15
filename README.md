Online Smartphone Trading Portal for CSE370

A web portal where users register their smartphones, get an automatic price estimate, list them on a marketplace, and trade phones with other users (with the cash difference calculated automatically). Includes a user review system and an admin panel.

> **Status:** Work in progress. Core features run; some parts are incomplete (see [Known Issues](#known-issues)).

## Tech Stack

- **Backend:** PHP (mysqli)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Local server:** XAMPP (Apache + MySQL)

## Features

### 1. Phone Valuation
- Register a phone (model, storage, battery health, condition)
- Condition options: Excellent, Good, Fair, Poor
- Automatic price estimate from the model's base price, age-based depreciation, a condition multiplier and battery health
- View all registered phones with their estimated prices

### 2. Marketplace
- List a registered phone on the marketplace
- Browse active listings (brand, model, year, condition, price, owner)
- Search by brand or model
- Filter by condition, release year and price range
- Compare your phone's price against a listed phone (gain/loss)

### 3. Trade Negotiation
- Send a trade request (your phone for a target phone) with automatic cash-difference calculation
- View sent requests and their status
- View incoming requests
- Cancel a pending request

### 4. Trade Execution
- Accept or reject incoming requests
- On acceptance: ownership of both phones is swapped, both are marked *Traded*, their listings are closed, conflicting pending requests are rejected, and a transaction record is created
- Transaction history showing phones traded and cash paid/received

### 5. Trust & Ratings
- Log in with role-based redirect (user / admin)
- Leave a 1-5 star rating and comment for another user (one review per user pair, no self-reviews)
- View reviews and the average rating on a user's profile

### 6. Admin Panel
- View and filter users by role
- Activate / deactivate marketplace listings
- Update the base price of phone models
- View all trades across the platform, filtered by status
- Reports: total users, active/inactive listings, most listed brands

## Setup (Windows + XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this project folder into `C:\xampp\htdocs\` and name it **`portal`** (the code uses `/portal/` paths, so the folder name matters).
3. Open `http://localhost/phpmyadmin`, create a database, and **import `database.sql`**.
4. Open `includes/db.php` and make sure the database name matches the one you created. The default XAMPP login is user `root` with an empty password.
5. Visit `http://localhost/portal/login.php`.

> Test login details: *add a sample admin and user from your database here.*

## Project Structure

```
portal/
├── includes/           # db.php, nav.php, style.css, price_calc.php
├── login.php / logout.php
├── register_phone.php  # phone registration + price estimate
├── my_phones.php / view_phones.php
├── marketplace.php     # browse, search, filter, compare, list phone
├── trade.php           # send trade request
├── my_requests.php     # sent requests, cancel
├── incoming.php        # accept / reject requests
├── transactions.php    # transaction history
├── review.php / profile.php
├── admin_dashboard.php, admin_users.php, admin_listings.php,
│   admin_models.php, admin_trades.php, admin_reports.php
└── database.sql
```

## Team Contributions

| Member | Contribution |
|--------|--------------|
| **mmaliyatmarouf** | Phone registration system, user review system |
| *Teammate name* | *e.g. Marketplace and search* |
| *Teammate name* | *e.g. Trade requests and execution* |

## Known Issues

- No user sign-up page. Accounts must be added to the database by an admin (`register.php` is an unused leftover).
- Updating a phone's condition to re-evaluate its price (feature 1.4) and editing a profile (5.2) are not implemented.
- Reviews are not yet restricted to users who completed a trade together.
- The admin reports page does not yet show total trades, and `admin_trades.php` is not linked from the admin dashboard.
- Most admin pages only check that a user is logged in, not that they are an admin.
- Several queries are built from raw input rather than prepared statements.
- Passwords are compared in plain text.

These are planned fixes, and the portal is intended for local/demo use only.

## License

Educational project. Not for production use.
