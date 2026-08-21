# 🐾 Pet Care System

**University Web Application Development Project**

A fully integrated, database-backed Pet Care System supporting customer bookings and admin management.

---

## Project Overview

The Pet Care System is a multi-module web application that allows pet owners to browse services, book appointments, and complete simulated payments — while administrators manage the service catalog, monitor bookings, and view payment statuses.

---

## Features

### Pet Owner
- Browse pet care services catalog
- Search by service name
- Filter by service category
- Filter by pet type
- View detailed service information
- Book a service with pet and owner details
- Complete a simulated demo payment
- View booking confirmation with transaction reference

### Administrator
- Secure login with hashed passwords
- Admin dashboard with live statistics
- Create / Read / Update / Delete services
- View all customer appointments
- Monitor booking status and payment status
- Secure session-based authentication
- Protected logout

---

## Technologies Used

| Layer    | Technology |
|----------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend  | PHP (Procedural) |
| Database | MySQL (mysqli) |
| Version Control | Git / GitHub |

No frameworks (no Laravel, Bootstrap, React, etc.)

---

## Project Structure

```
pet-care-system/
├── index.php                    # Customer service catalog
├── service-details.php          # Service detail view
│
├── config/
│   └── db.php                   # Database connection
│
├── database/
│   ├── pet_care.sql             # Full schema + sample data
│   └── seed_admin.php           # Demo admin seeder
│
├── includes/
│   ├── header.php               # Shared page header
│   ├── footer.php               # Shared page footer
│   ├── functions.php            # Helper functions
│   └── auth.php                 # Session guard
│
├── customer/
│   ├── booking.php              # Booking form
│   ├── process_booking.php      # Booking handler
│   ├── payment.php              # Simulated payment
│   ├── process_payment.php      # Payment handler
│   └── booking-success.php      # Confirmation
│
├── admin/
│   ├── login.php                # Admin login
│   ├── authenticate.php         # Login handler
│   ├── logout.php               # Session destroy
│   ├── dashboard.php            # Dashboard & stats
│   ├── services.php             # Service list
│   ├── service-add.php          # Add service
│   ├── service-edit.php         # Edit service
│   ├── service-delete.php       # Delete handler
│   └── appointments.php         # Appointments view
│
└── assets/
    ├── css/
    │   ├── common.css           # Shared design system
    │   ├── customer.css         # Catalog styles
    │   ├── booking.css          # Booking/payment styles
    │   └── admin.css            # Admin styles
    ├── js/
    │   ├── catalog.js           # Catalog UX
    │   ├── booking.js           # Booking/payment UX
    │   └── admin.js             # Admin UX
    └── images/
        └── placeholder.svg      # Fallback image
```

---

## Database Setup

### 1. Import the schema and sample data

```bash
mysql -u root -p < database/pet_care.sql
```

This creates the `pet_care_system` database with all 4 tables and 5 sample services.

### 2. Configure the connection

Edit **`config/db.php`** and update these variables:

```php
$host        = 'localhost';
$db_user     = 'root';
$db_password = 'your_password';   // change this
$db_name     = 'pet_care_system';
```

---

## Admin Setup

### Seed the demo administrator

Start the server (see below), then visit:

```
http://localhost:8000/database/seed_admin.php
```

Or run via CLI:

```bash
php database/seed_admin.php
```

### Demo Admin Credentials

> ⚠️ **For coursework / demo purposes only. Do not use in production.**

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

Passwords are stored as bcrypt hashes (`password_hash()` / `password_verify()`). The plain-text password is never stored.

---

## Running the Project

From the project root:

```bash
php -S localhost:8000
```

---

## Application URLs

| Page                | URL |
|---------------------|-----|
| Customer Catalog    | http://localhost:8000/ |
| Service Details     | http://localhost:8000/service-details.php?id=1 |
| Book a Service      | http://localhost:8000/customer/booking.php?service_id=1 |
| Admin Login         | http://localhost:8000/admin/login.php |
| Admin Dashboard     | http://localhost:8000/admin/dashboard.php |

---

## Payment Notice

The payment system is a **simulated demo** for coursework purposes.

- No real financial transaction occurs
- No real card number, CVV, or PIN is stored or transmitted
- The "card number" input field is purely cosmetic and is not submitted to the server
- Transaction references are generated server-side (e.g. `PAY-20260821-153045-A8F4`)

---

## Database Schema

| Table         | Key Columns |
|---------------|-------------|
| `admins`      | id, username, password (bcrypt hash) |
| `services`    | id, service_name, category, target_pet_type, description, price, image |
| `appointments`| id, service_id (FK), customer_name, email, phone, pet_name, breed, age, appointment_date, booking_status |
| `payments`    | id, appointment_id (FK), amount, payment_method, transaction_reference, payment_status, paid_at |

Foreign keys:
- `appointments.service_id → services.id` (RESTRICT / CASCADE)
- `payments.appointment_id → appointments.id` (CASCADE / CASCADE)

---

## Team Contributions

| Member   | Branch                      | Responsibilities |
|----------|-----------------------------|-----------------|
| Member 1 | `member1-core-auth`         | Database schema, connection, shared helpers, header/footer, admin auth, dashboard |
| Member 2 | `member2-service-catalog`   | Service catalog, search, filters, service details |
| Member 3 | `member3-booking-payment`   | Booking form, payment simulation, confirmation |
| Member 4 | `member4-admin-management`  | Admin service CRUD, appointment monitoring |

---

## Git Branches

```
main                      ← Final stable
development               ← Integration
member1-core-auth
member2-service-catalog
member3-booking-payment
member4-admin-management
```

---

## Quick Test Flow

1. Import SQL → Seed admin → Start server
2. Go to `http://localhost:8000/` — catalog loads
3. Search "Grooming" — filtered results
4. Click **Book Now** → fill form → submit
5. Select payment method → Confirm Payment
6. Confirmation page shows booking details
7. Go to `http://localhost:8000/admin/login.php`
8. Login: `admin` / `admin123`
9. Dashboard shows updated statistics
10. Appointments page shows the booking as **Confirmed / Paid**
