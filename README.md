<div align="center">

# 🅿️ Smart Urban Parking System

**A full-stack PHP web application for managing urban parking spots, reservations, payments, and enforcement — built with a clean MVC architecture and zero external frameworks.**

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

</div>

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features by Role](#features-by-role)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Getting Started](#getting-started)
- [Configuration](#configuration)
- [Cron Job](#cron-job)
- [Design Patterns Used](#design-patterns-used)
- [Diagrams](#diagrams)
- [Contributing](#contributing)

---

## Overview

Smart Urban Parking System is a web-based parking management platform that connects **drivers** looking for parking, **space owners** who list their spots, **enforcement officers** who issue fines, and **municipal administrators** who oversee the entire ecosystem.

Key highlights:

- **Multi-role access** — four distinct portals (Driver, Owner, Officer, Admin) with role-based route protection
- **Real-time availability tracking** — buffer periods between bookings, conflict detection, and waitlist management
- **Dynamic pricing** — peak-hour multipliers, promo codes, loyalty tier discounts, and VAT-per-zone
- **Financial flows** — wallet top-ups, escrow-based payouts to owners, fine collection, and PDF invoice/report generation
- **Observer-driven notifications** — reservation events automatically fan out in-app notifications
- **Framework-free** — vanilla PHP 8.1+, a custom router, and PDO; no Composer dependencies required

---

## Features by Role

### 🚗 Driver
| Feature | Details |
|---------|---------|
| Spot search | Filter by zone, availability, EV charging, and vehicle dimensions |
| Booking | Hourly bookings with real-time conflict checking and buffer periods |
| Subscriptions | Weekly commuter subscriptions with recurring time slots |
| Booking management | View, extend, or cancel reservations; dispute resolution |
| Wallet | Top-up balance and pay with wallet or card |
| Promo codes | Apply promotional discounts at checkout |
| Loyalty programme | Bronze → Silver → Gold tiers based on rolling 30-day bookings |
| Fines & appeals | View issued fines and submit appeals |
| Favourites | Save frequently used spots |
| Vehicles | Manage multiple vehicle profiles (dimensions, EV capability) |
| Notifications | In-app notification centre |

### 🏠 Space Owner
| Feature | Details |
|---------|---------|
| Spot listing | Submit spots for admin approval with supporting documents |
| Spot management | Set pricing, buffer duration, and availability |
| Earnings | View payout history and request withdrawals |
| Reports | Download PDF earnings and booking reports |
| Verification | Upload ID and utility-bill documents for KYC |

### 👮 Enforcement Officer
| Feature | Details |
|---------|---------|
| Active bookings | View all currently active reservations in real time |
| Violation logging | Issue fines against drivers for parking violations |

### 🛡️ Admin
| Feature | Details |
|---------|---------|
| Dashboard | KPI overview — total spots, active reservations, revenue, pending tasks |
| Spot approvals | Approve or reject owner spot-listing submissions |
| Owner KYC | Approve or reject owner identity verification |
| Zone management | Create zones, set VAT rates, lock zones for events |
| Fine management | Review, adjust, and waive fines |
| Appeals | Adjudicate driver fine appeals |
| Booking disputes | Resolve disputes between drivers and owners |
| Heatmap | Visual occupancy heatmap across all zones |
| Notifications | Broadcast system-wide notifications |

---

## Architecture

```
index.php  ──►  Router  ──►  Controller  ──►  Model  ──►  Database (PDO)
                                 │
                                 └──►  View (PHP templates)
```

The application follows a **Model-View-Controller (MVC)** pattern with a thin custom router that maps HTTP method + path pairs to controller actions. There are no template engines — Views are plain PHP files rendered through a `View::render()` helper.

### Request lifecycle

1. **`index.php`** registers all routes and calls `Router::dispatch()`.
2. The router strips the script-name base prefix, matches method + path, and invokes the handler.
3. The controller authenticates the session via `Auth::requireRole()`, queries models, and calls `View::render()`.
4. Views receive a data array and produce HTML output.

---

## Project Structure

```
Parking-Slot/
├── Diagrams/                              # UML & system design artefacts
│   ├── Activity Diagrams.drawio
│   ├── Class_Object_Diagrams.drawio
│   ├── ERD Diagram.drawio
│   ├── Package Diagram.drawio
│   ├── SD.drawio                          # Sequence diagrams
│   ├── System Arch.drawio
│   ├── Use Case.drawio
│   └── SRS.docx                           # Software Requirements Specification
│
├── .env.example                           # Environment variable template
├── index.php                              # Front controller & route registry
├── bootstrap.php                          # App initialization & autoloader
├── cron_no_show.php                       # CLI script for no-show reservations
├── parking_db.sql                         # Database schema + seed data
├── parking_system_report.pdf              # Sample generated report
│
├── Core/                                  # Core framework utilities
│   ├── AppClock.php                       # Mockable clock for testing
│   ├── Auth.php                           # Authentication helpers
│   ├── Database.php                       # PDO database singleton
│   ├── Router.php                         # Lightweight routing system
│   ├── Session.php                        # Session management wrapper
│   ├── SimplePdf.php                      # PDF generation helper
│   └── View.php                           # Template rendering engine
│
├── Controllers/                           # Application controllers
│   ├── BaseController.php
│   ├── AdminController.php
│   ├── AuthController.php
│   ├── DriverController.php
│   ├── HomeController.php
│   ├── OfficerController.php
│   └── OwnerController.php
│
├── Models/                                # Business logic & data layer
│   ├── BookingManager.php
│   ├── ParkingBookingValidator.php
│   ├── ParkingAvailabilityTracker.php
│   ├── PricingEngine.php
│   ├── PricingModel.php
│   ├── TaxEngine.php
│   ├── PaymentModel.php
│   ├── PaymentMethodStrategy.php
│   ├── DriverWalletModel.php
│   ├── PromotionalCodeValidator.php
│   ├── PromoCode.php
│   ├── WaitlistModel.php
│   ├── ReviewModel.php
│   ├── PenaltyModel.php
│   ├── BookingDisputeModel.php
│   ├── SpotApprovalModel.php
│   ├── NotificationService.php
│   ├── ReservationSubject.php
│   ├── ReservationObserver.php
│   ├── ReservationEvent.php
│   ├── ReservationTimeService.php
│   ├── SubscriptionPeriodService.php
│   ├── OwnerReportModel.php
│   ├── ParkingSystemConfig.php
│   ├── User.php
│   └── ClassDiagramEntities.php
│
├── Views/                                 # Frontend templates
│   ├── layout/
│   │   ├── header.php
│   │   └── footer.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   │
│   ├── driver/
│   │   ├── dashboard.php
│   │   ├── search.php
│   │   ├── book.php
│   │   ├── bookings.php
│   │   ├── bookingdetail.php
│   │   ├── vehicles.php
│   │   ├── favorites.php
│   │   ├── fines.php
│   │   ├── notifications.php
│   │   └── zones.php
│   │
│   ├── owner/
│   │   ├── dashboard.php
│   │   ├── spots.php
│   │   ├── earnings.php
│   │   ├── reports.php
│   │   ├── verify.php
│   │   └── notifications.php
│   │
│   ├── officer/
│   │   ├── dashboard.php
│   │   └── violation.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── spots.php
│   │   ├── zones.php
│   │   ├── owners.php
│   │   ├── spot_approvals.php
│   │   ├── fines.php
│   │   ├── appeals.php
│   │   ├── booking_disputes.php
│   │   ├── heatmap.php
│   │   └── notifications.php
│   │
│   └── errors/
│       └── 404.php
│
├── assets/
│   └── css/
│       └── style.css
│
└── uploads/                               # User-uploaded content (gitignored)
    ├── docs/                              # Owner verification documents
    └── spot_docs/                         # Parking spot proof documents             # Parking spot proof documents
```

---

## Database Schema

The schema lives in `Smart-Urban-Parking-System/parking_db.sql`. It creates the `parking_db` database from scratch (safe to re-run: `DROP DATABASE IF EXISTS`).

**37 tables across several domains:**

| Domain | Tables |
|--------|--------|
| Users & roles | `users`, `drivers`, `space_owners`, `municipal_admins`, `enforcement_officers` |
| Vehicles | `vehicle_profiles` |
| Locations & zones | `locations`, `zones` |
| Parking spots | `parking_spots`, `buffer_manager`, `difficulty_ratings` |
| Pricing & tax | `pricing_engine`, `tax_engine` |
| Promotions | `promo_codes` |
| Payments | `payment_methods`, `escrow_service`, `payments`, `invoices`, `payouts`, `platform_account` |
| Reservations | `reservations`, `parking_sessions`, `subscriptions`, `waitlist` |
| Enforcement | `violation_detection`, `fines`, `appeals` |
| Disputes | `disputes` |
| Loyalty | `loyalty_accounts` |
| Notifications | `notifications` |
| Reporting | `revenue_repository`, `reports`, `heatmap_data`, `audit_log` |
| Documents | `document_repository` |
| Misc | `navigation_logs`, `favorite_spots`, `blacklist` |

---

## Getting Started

### Prerequisites

| Requirement | Version |
|------------|---------|
| PHP | 8.1 or higher |
| MySQL / MariaDB | 8.0 / 10.5 or higher |
| Web server | Apache (with `mod_rewrite`) or Nginx |
| XAMPP / Laragon | Any recent version (Windows) |

> **No Composer required** — the project has zero external PHP dependencies.

---

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/fadyselimz/Parking-Slot.git
cd Parking-Slot
```

**2. Import the database**

```bash
mysql -u root -p < Smart-Urban-Parking-System/parking_db.sql
```

Or via phpMyAdmin: import `Smart-Urban-Parking-System/parking_db.sql`.

**3. Configure the environment**

```bash
cp Smart-Urban-Parking-System/.env.example Smart-Urban-Parking-System/.env
```

Edit `.env` with your database credentials and preferred settings (see [Configuration](#configuration)).

**4. Configure your web server**

Point the document root to `Smart-Urban-Parking-System/`.

**Apache** — create or add to `.htaccess` inside `Smart-Urban-Parking-System/`:

```apache
Options -Indexes
RewriteEngine On

# Serve real files and directories directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Route everything else through the front controller
RewriteRule ^ index.php [QSA,L]
```

**Nginx** — add inside your `server {}` block:

```nginx
root /path/to/Smart-Urban-Parking-System;
index index.php;

location / {
    try_files $uri $uri/ /index.php$is_args$args;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass unix:/run/php/php8.1-fpm.sock; # adjust as needed
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

**5. Set upload directory permissions**

```bash
chmod -R 755 Smart-Urban-Parking-System/uploads/
```

**6. Open the application**

Navigate to `http://localhost/` (or your configured virtual host). You should see the home / login page.

---

### XAMPP Quick Start

1. Copy or symlink the `Smart-Urban-Parking-System/` folder into `C:\xampp\htdocs\parking\`
2. Import `parking_db.sql` via phpMyAdmin
3. Copy `.env.example` → `.env` (default credentials work with a stock XAMPP install)
4. Visit `http://localhost/parking/`

---

## Configuration

All runtime configuration is read from `Smart-Urban-Parking-System/.env`.  
The application falls back to sensible defaults if `.env` is absent.

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_TIMEZONE` | `Africa/Cairo` | PHP + MySQL session timezone |
| `DB_HOST` | `localhost` | MySQL host |
| `DB_PORT` | `3306` | MySQL port |
| `DB_NAME` | `parking_db` | Database name |
| `DB_USER` | `root` | Database user |
| `DB_PASS` | *(empty)* | Database password |
| `PEAK_MULTIPLIER` | `1.25` | Price multiplier during peak hours |
| `PENALTY_RATE_PER_MINUTE` | `0.5` | Fine accrual rate (currency units / min) |
| `DEFAULT_PROMO_VALIDITY_MONTHS` | `6` | Default promo-code expiry in months |
| `OWNER_REPORT_STATIC_PDF` | *(auto)* | Absolute path to owner report PDF; leave blank to use the bundled file |

> **Security:** Never commit your `.env` file. It is listed in `.gitignore`.

---

## Cron Job

The `cron_no_show.php` script marks overdue confirmed reservations as `no_show` and releases the spot back to available.

**Linux / macOS — run every 5 minutes:**

```cron
*/5 * * * * php /path/to/Smart-Urban-Parking-System/cron_no_show.php >> /var/log/parking_noshow.log 2>&1
```

**Windows Task Scheduler:**

```
Program: C:\xampp\php\php.exe
Arguments: C:\xampp\htdocs\parking\cron_no_show.php
```

---

## Design Patterns Used

| Pattern | Where |
|---------|-------|
| **MVC** | Global app structure |
| **Singleton** | `Database::getConnection()` — one PDO instance per request |
| **Front Controller** | `index.php` + `Router` |
| **Observer** | `ReservationSubject` / `ReservationObserver` / `ReservationEvent` — notification fan-out on booking events |
| **Strategy** | `PaymentMethodStrategy` — pluggable payment-method implementations |
| **Repository / Active Record hybrid** | `User`, `BookingManager`, `PaymentModel`, etc. |
| **Service Layer** | `ReservationTimeService`, `SubscriptionPeriodService`, `NotificationService` |
| **Value Object** | `PromoCode`, `ReservationEvent` |

---

## Diagrams

All UML and architecture diagrams are stored in `Diagrams/` as [draw.io](https://app.diagrams.net/) (`.drawio`) files. Open them with the draw.io desktop app or at [app.diagrams.net](https://app.diagrams.net/).

| File | Contents |
|------|----------|
| `Use Case.drawio` | Use-case diagram for all four roles |
| `Class_Object_Diagrams.drawio` | Full class & object diagrams |
| `ERD Diagram.drawio` | Entity-relationship diagram |
| `Activity Diagrams.drawio` | Booking, payment, and enforcement flows |
| `SD.drawio` | Key sequence diagrams |
| `Package Diagram.drawio` | Package-level architecture |
| `System Arch.drawio` | High-level system architecture |
| `SRS.docx` | Software Requirements Specification document |

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "feat: add your feature"`
4. Push to the branch: `git push origin feature/your-feature`
5. Open a Pull Request

Please make sure your `.env` file is never committed — use `.env.example` for sharing configuration templates.

---

<div align="center">

Made with ☕ and PHP — Smart Urban Parking System

</div>
