# ECO CONNECT — Production-Ready Architecture Design

## 1. System Vision

ECO CONNECT is a waste management platform for Gasabo District that allows customers to request waste collection, admins to manage operations, and cashiers to process payments and receipts. The system must behave like a professional RBAC-based application with isolated dashboards, secure authentication, and clear workflow enforcement.

## 2. Core Design Principles

- Every user must have their own account and password.
- No user should be able to choose a role manually at login.
- The system identifies the role from the database and redirects the user to the correct dashboard automatically.
- Admin is the only role that can create staff accounts.
- Customers register themselves and receive the Customer role automatically.
- Each role gets its own pages, sidebar menu, permissions, and database access pattern.
- The UI must be calm, modern, responsive, and environmentally themed.

## 3. Roles and Responsibilities

### Admin
Responsibilities:
- Manage users and access rights.
- Approve or deactivate customer accounts.
- Create cashier accounts.
- Manage waste categories and related images.
- Review and assign waste collection requests.
- Monitor status updates and collection progress.
- Manage payments, receipts, reports, messages, feedback, and settings.

Permissions:
- Full access to admin modules.
- Create, update, delete, and view all system records.
- View analytics and financial summaries.

Dashboard modules:
- Overview
- User management
- Customer management
- Cashier management
- Waste category management
- Requests management
- Collection monitoring
- Payments and receipts
- Reports
- Messages
- Notifications
- Feedback
- Settings
- Profile and logout

### Cashier
Responsibilities:
- Receive collection payments.
- Record cash, PayPal, or Mobile Money transactions.
- Generate receipts.
- View customer payment history.
- Review assigned collection records.
- Send messages and receive notifications.
- Manage own profile.

Permissions:
- Access payment, receipt, and customer payment modules.
- View assigned request records.
- Cannot create admin or customer accounts.
- Cannot edit waste categories or system settings.

Dashboard modules:
- Overview
- Payments
- Receipt generation
- Customer payment history
- Reports
- Messages
- Notifications
- Profile
- Logout

### Customer
Responsibilities:
- Register and log in securely.
- Create waste pickup requests.
- Select waste categories and upload images if needed.
- Track request progress.
- View collection status, assigned team info, and payment history.
- Download receipts.
- Send feedback and messages.
- Manage profile and notifications.

Permissions:
- Access only customer-specific pages.
- View and update own requests only.
- Cannot access admin or cashier modules.

Dashboard modules:
- Overview
- Request waste collection
- My requests
- Track request status
- Payment history
- Receipts
- Messages
- Feedback
- Notifications
- Profile
- Logout

## 4. Recommended Folder and File Structure

```text
eco_connect/
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── forgot_password.php
├── admin/
│   ├── dashboard.php
│   ├── customers.php
│   ├── cashiers.php
│   ├── manage_cashier.php
│   ├── requests.php
│   ├── categories.php
│   ├── payments.php
│   ├── receipts.php
│   ├── reports.php
│   ├── messages.php
│   ├── notifications.php
│   ├── feedback.php
│   ├── settings.php
│   ├── profile.php
│   └── logout.php
├── cashier/
│   ├── dashboard.php
│   ├── payments.php
│   ├── receipts.php
│   ├── customers.php
│   ├── reports.php
│   ├── messages.php
│   ├── notifications.php
│   ├── profile.php
│   └── logout.php
├── customer/
│   ├── dashboard.php
│   ├── request.php
│   ├── my_requests.php
│   ├── track_request.php
│   ├── payments.php
│   ├── receipts.php
│   ├── feedback.php
│   ├── messages.php
│   ├── notifications.php
│   ├── profile.php
│   └── logout.php
├── config/
│   ├── database.php
│   ├── session.php
│   └── permissions.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── functions.php
│   └── sidebar.php
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── uploads/
│   ├── waste_images/
│   └── receipts/
├── docs/
│   └── eco_connect_architecture.md
└── database/
    └── eco_connect_rbac_schema.sql
```

## 5. File Purpose and Database Operations

### Auth files
- auth/login.php
  - Validates email and password.
  - Reads the user record from users.
  - Verifies password hash.
  - Loads the role from the database.
  - Redirects to the correct dashboard.

- auth/register.php
  - Accepts customer registration data.
  - Inserts a new row in users with role = customer and status = pending.
  - Sends notification to admin.

- auth/logout.php
  - Clears session data.
  - Ends the current authenticated session.

### Admin files
- admin/dashboard.php
  - Reads summary statistics.
  - Counts customers, requests, pending requests, payments, and completed collections.

- admin/customers.php
  - Reads and updates customer rows.
  - Activates or deactivates customer accounts.

- admin/cashiers.php
  - Reads cashier accounts.
  - Updates status and permissions.

- admin/manage_cashier.php
  - Inserts or updates cashier account details.
  - Assigns role and password.

- admin/requests.php
  - Reads waste requests.
  - Updates request status and assigns collectors or cashiers.

- admin/categories.php
  - Inserts, updates, and deletes waste categories.
  - Stores category image path.

- admin/payments.php
  - Reads payment records and updates payment status.

- admin/receipts.php
  - Generates and views receipts linked to payments.

- admin/reports.php
  - Reads aggregated data for operations and financial reports.

- admin/messages.php
  - Reads and sends messages between users.

- admin/notifications.php
  - Reads and marks notifications as read.

- admin/feedback.php
  - Views customer feedback and service ratings.

- admin/settings.php
  - Stores system-wide settings and company profile data.

### Cashier files
- cashier/dashboard.php
  - Reads payment and request summary data.

- cashier/payments.php
  - Inserts payment records.
  - Updates payment status.
  - Generates receipts.

- cashier/receipts.php
  - Reads and prints receipts.

- cashier/customers.php
  - Reads customer payment and request history.

- cashier/reports.php
  - Reads cashier-specific reports.

### Customer files
- customer/dashboard.php
  - Reads own request statistics and recent activity.

- customer/request.php
  - Creates a new waste collection request.
  - Stores selected waste category and location.

- customer/my_requests.php
  - Reads historical requests for the logged-in customer.

- customer/track_request.php
  - Reads current request status and follow-ups.

- customer/payments.php
  - Reads payment records for the customer.

- customer/receipts.php
  - Reads receipts for completed payments.

- customer/feedback.php
  - Inserts new feedback after request completion.

- customer/messages.php
  - Sends and reads messages with admin or cashier.

## 6. Recommended Database Structure

### 1. users
Purpose: stores all login accounts for the platform.

Columns:
- user_id (PK)
- username
- email (unique)
- password_hash
- full_name
- phone
- address
- role
- status
- created_at
- updated_at
- last_login

Relationships:
- One user can have many requests.
- One user can have many messages.
- One user can have many notifications.
- One user can have many payments.

### 2. roles
Purpose: central role catalog.

Columns:
- role_id (PK)
- role_name (admin, cashier, customer)
- description

Relationships:
- One role can be assigned to many users.

### 3. customers
Purpose: customer profile details beyond the shared user table.

Columns:
- customer_id (PK)
- user_id (FK -> users.user_id)
- district
- sector
- cell
- village
- preferred_collection_time
- profile_image

### 4. employees
Purpose: staff records for cashiers and other staff.

Columns:
- employee_id (PK)
- user_id (FK -> users.user_id)
- employee_code
- department
- hire_date
- is_active

### 5. waste_categories
Purpose: categories of waste that can be requested.

Columns:
- category_id (PK)
- category_name
- description
- image_path
- price_per_kg
- is_active
- created_at

### 6. waste_requests
Purpose: waste collection requests made by customers.

Columns:
- request_id (PK)
- customer_id (FK -> customers.customer_id)
- category_id (FK -> waste_categories.category_id)
- request_code
- waste_quantity_kg
- pickup_address
- preferred_date
- request_notes
- status
- assigned_to (FK -> users.user_id, nullable)
- approved_by (FK -> users.user_id, nullable)
- created_at
- updated_at

### 7. request_images
Purpose: images attached to waste requests.

Columns:
- image_id (PK)
- request_id (FK -> waste_requests.request_id)
- image_path
- uploaded_at

### 8. payments
Purpose: payment records for collection requests.

Columns:
- payment_id (PK)
- request_id (FK -> waste_requests.request_id)
- customer_id (FK -> users.user_id)
- cashier_id (FK -> users.user_id, nullable)
- amount
- payment_method
- transaction_reference
- payment_status
- paid_at
- created_at

### 9. receipts
Purpose: professional payment receipts.

Columns:
- receipt_id (PK)
- payment_id (FK -> payments.payment_id)
- receipt_number
- issued_at
- pdf_path
- created_at

### 10. messages
Purpose: communication between users.

Columns:
- message_id (PK)
- sender_id (FK -> users.user_id)
- receiver_id (FK -> users.user_id)
- subject
- message_content
- is_read
- created_at

### 11. notifications
Purpose: system notifications for important events.

Columns:
- notification_id (PK)
- user_id (FK -> users.user_id)
- title
- message
- type
- link
- is_read
- created_at

### 12. followups
Purpose: status updates for customer requests.

Columns:
- followup_id (PK)
- request_id (FK -> waste_requests.request_id)
- updated_by (FK -> users.user_id)
- status
- note
- created_at

### 13. feedback
Purpose: customer feedback after completed jobs.

Columns:
- feedback_id (PK)
- request_id (FK -> waste_requests.request_id)
- customer_id (FK -> users.user_id)
- rating
- comments
- created_at

### 14. locations
Purpose: address and geographic data for requests.

Columns:
- location_id (PK)
- request_id (FK -> waste_requests.request_id)
- district
- sector
- cell
- village
- latitude
- longitude
- created_at

### 15. reports
Purpose: generated summaries or exported reports.

Columns:
- report_id (PK)
- generated_by (FK -> users.user_id)
- report_type
- report_period
- file_path
- generated_at

## 7. Waste Management Workflow

1. Customer registers and logs in.
2. Customer creates a waste request.
3. Customer selects waste category and pickup location.
4. Admin reviews the request.
5. Admin approves or rejects the request.
6. If approved, the request is assigned to a collection team or cashier workflow.
7. The request status moves through Pending, Approved, Assigned, In Progress, Completed, or Cancelled.
8. Customer can track the request and receive follow-ups.
9. After completion, the customer can rate the service and leave feedback.
10. Payment is recorded and a receipt is generated.

## 8. Recommended Request Status Lifecycle

- Pending: created but not yet reviewed.
- Approved: accepted by admin.
- Assigned: assigned to a team or operational flow.
- In Progress: collection is actively happening.
- Completed: job finished successfully.
- Cancelled: cancelled by admin, customer, or system.

## 9. UI/UX Recommendations

### Sidebar structure
- Admin sidebar: Dashboard, Customers, Cashiers, Waste Categories, Requests, Payments, Receipts, Reports, Messages, Notifications, Feedback, Settings, Profile, Logout.
- Cashier sidebar: Dashboard, Payments, Receipts, Customer History, Reports, Messages, Notifications, Profile, Logout.
- Customer sidebar: Dashboard, New Request, My Requests, Track Request, Payments, Receipts, Feedback, Messages, Notifications, Profile, Logout.

### Visual style
- Primary colors: deep green, dark forest green, eco gray, soft white.
- Accents: lime green and muted teal.
- Use calm, professional environmental colors rather than bright saturated tones.

### Layout patterns
- Dashboard cards with icons and trend indicators.
- Tables with dark headers and clean rows.
- Responsive spacing and consistent card padding.
- Clear action buttons with hover states.
- Notification badges for unread messages and updates.

## 10. Security Design

- Use password_hash() with bcrypt.
- Use prepared statements for all queries.
- Store sessions securely and regenerate IDs.
- Protect each page with role-based access checks.
- Validate all user input.
- Restrict file uploads and store them in dedicated directories.
- Log major actions such as login, request updates, payment confirmation, and account activation.

## 11. Implementation Order

1. Authentication and session management.
2. Users and role table design.
3. Admin dashboard and user management.
4. Customer registration and customer dashboard.
5. Waste category management and request creation.
6. Request workflow and status updates.
7. Payment and receipt flow.
8. Messages, notifications, and follow-ups.
9. Feedback and reporting.
10. Final UI polish and responsive testing.

## 12. Recommended Development Sequence for This Project

- First: fix authentication so admin, cashier, and customer each use their own credentials.
- Second: ensure the login page redirects automatically based on user role.
- Third: create the admin and customer modules first because they form the core workflow.
- Fourth: add cashier payment and receipt handling.
- Fifth: complete reporting, messages, feedback, and notifications.

## 13. Final Architecture Summary

The redesigned ECO CONNECT platform should be built as a true RBAC system where:
- Admin manages the entire ecosystem.
- Cashier handles payments and receipts.
- Customer creates requests and tracks their service.
- Each role has separate files, dashboard modules, database access patterns, and UI experience.

This structure makes the project feel like a real waste management platform rather than a simple demo.
