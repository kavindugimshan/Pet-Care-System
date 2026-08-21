# Pet Care System Project

University Web Application Development Practical project structure.

Tech stack: HTML, CSS, Vanilla JavaScript, PHP, MySQL.

---

## Member 1 — Setup Instructions (Core Auth)

### 1. Database Setup

Import the database schema (creates the database + all tables + sample services):

```bash
mysql -u root -p < database/pet_care.sql
```

### 2. Configure the Database Connection

Edit `config/db.php` and update these four variables to match your local environment:

```php
$host        = 'localhost';
$db_user     = 'root';
$db_password = '';        // your MySQL password
$db_name     = 'pet_care_system';
```

### 3. Seed the Demo Admin Account

Run the seeder once to create the hashed admin record:

```bash
php -S localhost:8000
```

Then visit in your browser:

```
http://localhost:8000/database/seed_admin.php
```

### 4. Demo Admin Credentials

> **For coursework / demo purposes only. Never use in production.**

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

### 5. Run the Development Server

From the project root:

```bash
php -S localhost:8000
```

### 6. Access the Application

| Page              | URL                                          |
|-------------------|----------------------------------------------|
| Home / Catalog    | http://localhost:8000/index.php              |
| Admin Login       | http://localhost:8000/admin/login.php        |
| Admin Dashboard   | http://localhost:8000/admin/dashboard.php    |
| Admin Seed Script | http://localhost:8000/database/seed_admin.php |

### 7. Testing Admin Login

1. Start the PHP server: `php -S localhost:8000`
2. Import the SQL: `mysql -u root -p < database/pet_care.sql`
3. Seed the admin: visit `http://localhost:8000/database/seed_admin.php`
4. Go to `http://localhost:8000/admin/login.php`
5. Log in with `admin` / `admin123`
6. Verify the dashboard shows **5 services**, **0 appointments**, etc.
7. Click Logout and confirm you are returned to the login page.
8. Try accessing `http://localhost:8000/admin/dashboard.php` directly — you should be redirected to login.

---

## Branch Strategy

| Branch                  | Responsibility             |
|-------------------------|----------------------------|
| `main`                  | Final stable release       |
| `development`           | Integration branch         |
| `member1-core-auth`     | Member 1 — Auth & Database |
| `member2-service-catalog` | Member 2 — Customer catalog |
| `member3-booking-payment` | Member 3 — Booking & payment |
| `member4-admin-management` | Member 4 — Admin management |

---

## Database Contract (All Members)

The following table/column names must **not** be renamed:

```
admins          → id, username, password, created_at
services        → id, service_name, category, target_pet_type, description, price, image
appointments    → id, service_id, customer_name, customer_email, customer_phone,
                   pet_name, breed, age, appointment_date, booking_status, created_at
payments        → id, appointment_id, amount, payment_method, transaction_reference,
                   payment_status, paid_at
```

Foreign key relationships:

```
services.id → appointments.service_id (RESTRICT / CASCADE)
appointments.id → payments.appointment_id (CASCADE / CASCADE)
```
Our Website
<img width="1884" height="881" alt="image" src="https://github.com/user-attachments/assets/6142190d-21f4-4002-83a8-7632094e137a" />
<img width="1884" height="881" alt="image" src="https://github.com/user-attachments/assets/a597c08f-2366-48f6-bde2-7dbe15b5043b" />
<img width="1884" height="881" alt="image" src="https://github.com/user-attachments/assets/fd96ec4a-f64a-4ff2-9e58-b4a9e939ff3c" />
<img width="1884" height="881" alt="image" src="https://github.com/user-attachments/assets/75d5efb9-e566-4c75-b87f-a4a32dce0b71" />
<img width="1884" height="881" alt="image" src="https://github.com/user-attachments/assets/017587db-d739-442e-95f5-c1d5987a4549" />
<img width="1884" height="881" alt="image" src="https://github.com/user-attachments/assets/07e2e42f-3aa6-4b61-a2c0-c6fe3f87a907" />






