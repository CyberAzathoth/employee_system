# EMPLOYEE MANAGEMENT SYSTEM
## Role-Based Access Control with Complete Audit Trail

---

Quick Start

### Installation (4 Steps):
1. Install XAMPP and start Apache + MySQL
2. Copy files to `htdocs/` folder and create another folder inside with your own preferred folder name
3. Run the setup.php first on your browser 
4. run the login.php to login

### Test Accounts:

Here are the test accounts we can use.

| Role | Username | Password | Permissions |
|------|----------|----------|-------------|
| Super Admin | `superadmin` | `admin123` | Full Access |
| Admin | `admin1` | `password123` | All except create Super Admin |
| Manager | `manager1` | `password123` | View, Add, Edit only |

---

## Features

### Complete Modules:
1. **Employee Management** - Add, Edit, Delete (role-based)
2. **User Management** - Create accounts, assign roles
3. **Password Logs** - Track all password changes & login attempts
4. **Deleted Employees** - Soft delete with audit trail (no restore)
5. **Activity Logs** - Track all system activities

### Security Features:
- ✓ **Login Required** - All pages protected except login
- ✓ **Password Hashing** - Bcrypt encryption
- ✓ **Login Attempt Limiting** - 5 attempts → 30s lockout
- ✓ **Warning at 3 Attempts** - "2 more attempts will lock account"
- ✓ **SQL Injection Prevention** - PDO prepared statements
- ✓ **XSS Prevention** - Output sanitization
- ✓ **Role-Based Access** - Manager/Admin/Super Admin
- ✓ **Complete Audit Trail** - All actions logged

---

## User Roles & Permissions

### Manager:
- Can View employees
- Can Add employees
- Can Edit employees
- Cannot delete
- Cannot manage users
- Limited log access

### Admin:
- Have All Manager permissions
- Can Delete employees
- Can Manage users
- Can View all logs
- Can Access deleted records
- Cannot create Super Admins

### Super Admin:
- Have Full system access
- Can Create Super Admin accounts
- Have All Admin permissions

---

##  Password & Login Tracking

### Login Attempt Rules:
```
Attempt 1-2: Normal error message
Attempt 3:   WARNING - "2 more attempts will lock account"
Attempt 4:   WARNING continues
Attempt 5:   LOCKED for 30 seconds
```

### What's Logged:
- All login attempts (success/failed/locked)
- Password creations and changes
- Who changed passwords
- IP addresses
- Timestamps

### Where to View:
Navigate to **Password Logs** in sidebar:
- Tab 1: Password Changes History
- Tab 2: Login Attempts with statistics

---

## Deleted Employees

### How It Works:
- Employees are **soft deleted** (not permanently removed)
- Records moved to "Deleted Employees" section
- Tracks:
  - Who deleted the record
  - When it was deleted
  - Days since deletion
- **NO RESTORE FUNCTION** - serves as audit log only

### Access:
Admin and Super Admin only → Sidebar → "Deleted Employees"

---

## Database Structure

### Tables Created:
1. **Employee** - Employee records with soft delete
2. **User** - User accounts with roles and lockout tracking
3. **PasswordLog** - Password change history
4. **LoginAttemptLog** - Login attempt tracking
5. **ActivityLog** - All system activities

### Relationships:
- Employee → User (1:Many)
- User → PasswordLog (1:Many)
- User → LoginAttemptLog (1:Many)
- User → ActivityLog (1:Many)

---

## Technologies Used

**Backend:**
- PHP 7.4+ (Server logic)
- MySQL 5.7+ (Database)
- PDO (Secure database access)

**Frontend:**
- HTML5 (Structure)
- CSS3 (Styling)
- JavaScript (Interactions)

**Security:**
- Bcrypt password hashing
- Prepared statements
- Session management
- Role-based access control

---

## ⚙️ System Requirements

- XAMPP/WAMP/MAMP
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- Modern web browser

---

