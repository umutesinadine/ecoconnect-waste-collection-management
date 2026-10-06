# EcoConnect First-Time Setup Checklist

## 1. Start XAMPP
- Open XAMPP Control Panel
- Start Apache
- Start MySQL

## 2. Create the database
- Open http://localhost/phpmyadmin
- Create a database named `ecoconnect`

## 3. Import the SQL file
- Go to Import in phpMyAdmin
- Choose the file `ecoconnect.sql`
- Click Go

## 4. Create a test admin account
- Open this URL in your browser:
  http://localhost/eco_connect/setup_test_admin.php

## 5. Log in
Use:
- Email: testadmin@ecoconnect.rw
- Password: password123

## 6. Open the dashboard
- Admin: http://localhost/eco_connect/admin/dashboard.php
- Manager: http://localhost/eco_connect/manager/dashboard.php
- Collector: http://localhost/eco_connect/collector/dashboard.php
- Customer: http://localhost/eco_connect/customer/dashboard.php

If Apache or MySQL is not running, the site will not load.
