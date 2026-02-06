# Vanilla PHP BLOG Application 

**This project is currently in development. Check back for updates !**

![Work in Progress](https://img.shields.io/badge/Status-In%20Progress-yellow)

#### This simple structure helps developers understand the system faster.

## Features

- **User Authentication**: Secure login/registration with password hashing
- **Role-Based Access Control (RBAC)**: Fine-grained permission system
- **Article Management**: Create, read, update, delete articles with categories
- **Category Management**: Organize articles into categories
- **Admin Panel**: Manage users, roles, permissions, and categories
- **Security**: CSRF protection, prepared statements, session security, XSS prevention
- **Responsive Design**: Clean, modern interface
- **No Framework**: Pure PHP implementation for educational purposes

## Quick Start

### Prerequisites
- PHP 7.4+ with MySQLi extension
- MySQL/MariaDB database
- Web server (Apache/Nginx) or XAMPP/LAMPP

### Installation

1. **Clone or extract the project** to your web server directory:
   ```bash
   cd /opt/lampp/htdocs  # For XAMPP/LAMPP on Linux
   git clone <repository-url> php-blog-vanilla
   ```

2. **Configure environment**:
   ```bash
   cd php-blog-vanilla
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Set up database**:
   ```bash
   # Create database (if not exists)
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS blog;"

   # Import schema
   mysql -u root -p blog < blog.sql
   ```

4. **Create super admin**:
   - Navigate to `http://localhost/php-blog-vanilla/auth/setup_super_admin.php`
   - Fill in super admin details
   - **IMPORTANT**: Delete `auth/setup_super_admin.php` after creation

5. **Access the application**:
   - Homepage: `http://localhost/php-blog-vanilla/`
   - Login with super admin credentials
   - Access Admin Panel from navigation menu

## Project Structure

```
vanilla-pure-php-crud-application/
├── config/                    # Configuration files
│   ├── app.php               # Application constants
│   ├── config.php            # Main loader
│   ├── connection.php        # Database connection
│   └── env.php               # Environment variable loader
├── includes/                 # Core includes
│   ├── bootstrap.php         # Bootstrap loader
│   ├── session.php          # Session management
│   ├── csrf.php             # CSRF protection
│   ├── auth.php             # Authentication helpers
│   ├── header.php           # Page header
│   ├── footer.php           # Page footer
│   └── navbar.php           # Navigation bar
├── src/Helpers/             # Helper functions
│   ├── url.php              # URL helper
│   ├── flash.php            # Flash messages
│   ├── sanitize.php         # HTML escaping
│   └── permissions.php      # RBAC functions
├── action/                  # Form submission handlers
│   ├── login_action.php     # Login processing
│   ├── register_action.php  # Registration processing
│   └── article/             # Article CRUD actions
├── admin/                   # Admin panel (RBAC management)
│   ├── index.php           # Admin dashboard
│   ├── roles.php           # Role management
│   ├── permissions.php     # Permission management
│   ├── user_roles.php      # User role assignment
│   └── role_permissions.php # Role permission assignment
├── pages/                   # Application pages
│   ├── article_add_form.php    # Add article form
│   ├── article_edit_form.php   # Edit article form
│   ├── article_view_form.php   # View articles list
│   ├── categories.php          # Category management
│   ├── category_add.php        # Add category form
│   ├── category_edit.php       # Edit category form
│   └── category_delete_action.php # Delete category action
├── assets/                  # Static resources
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript
│   └── images/             # Images
├── *.php                   # Public pages (home.php, auth/*.php, etc.)
├── blog.sql               # Complete database schema
├── database_articles_categories_migration.sql # Migration script
├── .env.example           # Environment template
└── README.md             # This file
```

**Note**: Application pages (articles and categories) are organized in the `pages/` directory for better structure, while RBAC management remains in `admin/`.

## User Roles and Permissions

### Default Roles
1. **Super Admin**: Full system access (all permissions automatically)
2. **Admin**: Manage users, articles, categories; access admin panel
3. **Manager**: Manage articles, view users
4. **User**: Basic article operations (view, create, edit own articles)

### Permission System
Permissions follow `module.action` format:
- **User management**: `users.view`, `users.create`, `users.edit`, `users.delete`, `users.manage_roles`
- **Article management**: `articles.view`, `articles.create`, `articles.edit`, `articles.delete`
- **Category management**: `categories.view`, `categories.create`, `categories.edit`, `categories.delete`
- **Role management**: `roles.view`, `roles.create`, `roles.edit`, `roles.delete`
- **Permission management**: `permissions.view`, `permissions.create`, `permissions.edit`, `permissions.delete`, `permissions.manage_role_permissions`
- **Admin access**: `admin.access`

## Using the Application

### For Regular Users
1. **Register** for an account
2. **Login** with your credentials
3. **View articles** from the main page
4. **Create articles** (if you have `articles.create` permission)
5. **Edit/delete** your own articles (with appropriate permissions)

### For Administrators
1. **Login** as admin or super admin
2. **Access Admin Panel** from navigation
3. **Manage roles, permissions, users, and categories**
4. **Assign permissions** to roles via Role Permissions
5. **Assign roles** to users via User Roles

### Article Management
1. **Create article**: Fill in title, excerpt, description, select category
2. **Edit article**: Modify existing articles
3. **Delete article**: Remove articles (requires confirmation)
4. **Categories**: Articles are organized into categories for better organization

## Security Features

### Database Security
- **Prepared statements** for all database queries
- **Password hashing** using `password_hash()` with bcrypt
- **Environment variables** for sensitive credentials
- **SQL injection prevention** through parameterized queries

### Session Security
- **Secure cookie settings** (httponly, samesite=strict)
- **Session timeout** (30 minutes inactivity)
- **Periodic session ID regeneration** (every 5 minutes)
- **Session hijacking prevention**

### CSRF Protection
- **Token generation** for all forms
- **Token validation** on form submission
- **Token rotation** after successful POST requests
- **Prevents replay attacks**

### XSS Prevention
- **Output escaping** with `htmlspecialchars()`
- **Content sanitization** using `e()` helper function
- **Safe HTML output** in templates

## Code Examples

### Checking Permissions
```php
<?php
require_once __DIR__ . '/config/config.php';

// Check if user has permission
if (current_user_has_permission('articles.delete')) {
    // Allow delete operation
}

// Require permission (redirects if unauthorized)
require_permission('users.create');

// Check role
if (current_user_has_role('admin')) {
    // Admin-specific functionality
}
?>
```

### Creating a New Page
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_permission('module.action'); // Optional permission check
// OR: require_auth(); // Just require authentication

$page_title = "Page Title";
require INCLUDES_PATH . '/header.php';
?>

<!-- Page content here -->

<?php require INCLUDES_PATH . '/footer.php'; ?>
```

### Form with CSRF Protection
```php
<form method="post" action="<?= htmlspecialchars(url('action/process.php')) ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <!-- Form fields -->

    <button type="submit">Submit</button>
</form>
```

## Database Schema

### Core Tables
- **login**: User accounts (id, name, email, username, password, is_active)
- **articles**: Articles with categories (id, title, slug, excerpt, description, status, user_id, category_id)
- **categories**: Article categories (id, name, description, slug)

### RBAC Tables
- **roles**: Role definitions (id, name, slug, description, is_system)
- **permissions**: Permission definitions (id, name, slug, description, module)
- **role_permissions**: Many-to-many role-permission assignments
- **user_roles**: Many-to-many user-role assignments

## Migration from Older Versions

If upgrading from a version with `products` table instead of `articles`:
```bash
mysql -u root -p blog < database_articles_categories_migration.sql
```

This migration:
1. Drops old `products` table
2. Creates `articles` and `categories` tables
3. Updates permissions from `products.*` to `articles.*`
4. Adds category permissions
5. Seeds initial categories

## Troubleshooting

### Common Issues

1. **Permission issues after database changes**
   - Solution: Logout and login again to refresh session permissions

2. **Category management not visible to super admin**
   - Verify super admin role assignment
   - Logout and login to refresh permission cache

3. **Database connection errors**
   - Check `.env` file exists with correct credentials
   - Verify database server is running
   - Ensure database name matches in `.env` and `connection.php`

4. **Form submission errors**
   - Ensure CSRF token is included in all POST forms
   - Check JavaScript console for errors

### Development Tips
- **Local environment**: Set `APP_ENV=local` in `.env` for error display
- **Debugging**: Check PHP error logs in XAMPP/LAMPP
- **Session issues**: Clear browser cookies or use incognito mode
