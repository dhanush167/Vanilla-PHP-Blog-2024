# 🚀 Vanilla PHP Blog - Pure, Powerful, Educational

> **Status:** 🔨 Actively crafting something awesome!  
> ![Work in Progress](https://img.shields.io/badge/Status-In%20Progress-yellow)

Welcome to a blog application that proves you don't need a fancy framework to build something secure, scalable, and elegant. This is pure PHP at its finest—perfect for learning, teaching, or just appreciating the beauty of well-structured code.

---

## ✨ What Makes This Special?

Think of this as your PHP learning playground with real-world features:

**🔐 Fort Knox Security**
- Password hashing that would make a cryptographer proud
- CSRF tokens guarding every form like digital bouncers
- XSS prevention keeping the bad scripts out
- Sessions locked down tighter than your favorite coffee shop's WiFi

**👥 Smart User Management**
- Role-based access control that actually makes sense
- Granular permissions (because not everyone should delete everything)
- Super admin powers when you need them

**📝 Content That Flows**
- Create, edit, and organize articles with style
- Categories that keep your content tidy
- Clean interfaces that don't make your eyes bleed

**🎓 Learning-Friendly**
- Zero frameworks—just honest-to-goodness PHP
- Clear structure that won't make you cry
- Comments where they actually help

---

## 🎬 Get Started in 5 Minutes

### What You'll Need
- PHP 7.4+ (with MySQLi—the trusty sidekick)
- MySQL/MariaDB (the data guardian)
- A web server (Apache, Nginx, or good ol' XAMPP/LAMPP)

### The Quick Setup Dance

**Step 1: Grab the Code**
```bash
cd /opt/lampp/htdocs  # Your web server's happy place
git clone <repository-url> php-blog-vanilla
```

**Step 2: Configure Your Secret Sauce**
```bash
cd php-blog-vanilla
cp .env.example .env
# Pop open .env and add your database credentials
```

**Step 3: Wake Up the Database**
```bash
# Birth a new database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS blog;"

# Feed it the schema
mysql -u root -p blog < blog.sql
```

**Step 4: Crown Your Super Admin**
- Navigate to `http://localhost/php-blog-vanilla/auth/setup_super_admin.php`
- Fill in the chosen one's details
- **⚠️ CRITICAL**: Delete `auth/setup_super_admin.php` afterward (security first!)

**Step 5: Take It for a Spin**
- 🏠 Homepage: `http://localhost/php-blog-vanilla/`
- 🔑 Login with your shiny new super admin credentials
- 🎛️ Access the Admin Panel and feel the power

---

## 🗂️ The Architecture (Clean & Mean)

```
📦 php-blog-vanilla
├── 🎬 action/              # Where forms come to process
│   ├── article/            # Article CRUD operations
│   ├── login_action.php
│   └── register_action.php
├── 👑 admin/               # The control center
│   ├── index.php           # Admin dashboard
│   ├── permissions.php     # Permission management
│   ├── roles.php           # Role management
│   └── user_roles.php      # User-role assignments
├── 🎨 assets/              # The pretty stuff
│   ├── css/
│   └── js/
├── 🔐 auth/                # The gateway
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   └── setup_super_admin.php
├── ⚙️ config/              # The brain
│   ├── app.php
│   ├── config.php
│   ├── connection.php
│   └── env.php
├── 🏠 home.php             # Sweet home page
├── 📋 includes/            # The helpers
│   ├── auth.php
│   ├── csrf.php
│   ├── header.php
│   ├── navbar.php
│   └── session.php
├── 📄 pages/               # Content pages
│   ├── article_*.php       # Article forms & views
│   └── category_*.php      # Category management
└── 🛠️ src/Helpers/        # Utility belt
    ├── flash.php
    ├── permissions.php
    ├── sanitize.php
    └── url.php
```

---

## 🎭 Roles & Permissions (The Power Hierarchy)

### Meet the Cast

**🦸 Super Admin** - *The Chosen One*
- Can literally do everything
- All permissions unlock automatically
- Use wisely (with great power...)

**👔 Admin** - *The Manager*
- Wrangles users, articles, and categories
- Has the admin panel keys
- Keeps things running smoothly

**📊 Manager** - *The Coordinator*
- Manages articles and peeks at user lists
- Middle management at its finest
- Gets stuff done

**👤 User** - *The Creator*
- Views articles, creates content
- Edits their own masterpieces
- The foundation of your community

### The Permission Matrix

Permissions use the intuitive `module.action` format:

**User Wrangling**
- `users.view` → See the people
- `users.create` → Invite more people
- `users.edit` → Update people details
- `users.delete` → Remove people (carefully!)
- `users.manage_roles` → Assign the roles

**Article Mastery**
- `articles.view` → Read all the things
- `articles.create` → Write new content
- `articles.edit` → Polish existing articles
- `articles.delete` → Remove articles (no undo!)

**Category Control**
- `categories.view` → Browse categories
- `categories.create` → Add new categories
- `categories.edit` → Update category details
- `categories.delete` → Remove categories

**Role Management**
- `roles.view` → See all roles
- `roles.create` → Define new roles
- `roles.edit` → Modify role details
- `roles.delete` → Remove roles

**Permission Power**
- `permissions.view` → View all permissions
- `permissions.create` → Add new permissions
- `permissions.edit` → Update permissions
- `permissions.delete` → Remove permissions
- `permissions.manage_role_permissions` → Connect roles to permissions

**Admin Access**
- `admin.access` → The golden ticket to the admin panel

---

## 📖 User Guides

### 👤 For Content Creators

1. **Join the party**: Hit that Register button
2. **Enter the realm**: Login with your credentials
3. **Explore content**: Browse articles on the main page
4. **Share your voice**: Create articles (if you've got the `articles.create` permission)
5. **Polish your work**: Edit or delete your own creations

### 👑 For The Admins

1. **Assume control**: Login as admin or super admin
2. **Enter the command center**: Click Admin Panel in the nav
3. **Orchestrate the system**: Manage roles, permissions, users, and categories
4. **Grant powers**: Assign permissions to roles
5. **Build teams**: Assign roles to users

### ✍️ Article Management Flow

1. **Birth an article**: Fill in title, excerpt, description, pick a category
2. **Refine it**: Edit to perfection
3. **Let it go**: Delete when necessary (with great ceremony)
4. **Stay organized**: Use categories to keep everything tidy

---

## 🛡️ Security Arsenal

### Database Defenses
- **Prepared statements** everywhere (SQL injection's worst nightmare)
- **Password hashing** with bcrypt (no plaintext here!)
- **Environment variables** for secrets (they're called secrets for a reason)
- **Parameterized queries** all day, every day

### Session Fortification
- **Secure cookies** with httponly and samesite flags
- **Auto-timeout** after 30 minutes of Netflix binging
- **Session ID rotation** every 5 minutes (identity theft protection)
- **Hijacking prevention** built right in

### CSRF Guardian
- **Token generation** for every single form
- **Token validation** before processing
- **Token rotation** after successful submissions
- **Replay attack prevention** as a bonus

### XSS Shield
- **Output escaping** with `htmlspecialchars()` on steroids
- **Content sanitization** via the handy `e()` helper
- **Safe templates** that don't trust user input

---

## 💻 Code Snippets (Copy-Paste Magic)

### Permission Checking Made Easy
```php
<?php
require_once __DIR__ . '/config/config.php';

// The polite way - check first
if (current_user_has_permission('articles.delete')) {
    // Proceed with deletion
}

// The firm way - redirect if unauthorized
require_permission('users.create');

// The role check
if (current_user_has_role('admin')) {
    // Admin-only awesomeness
}
?>
```

### Spinning Up a New Page
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_permission('module.action'); // Lock it down
// OR: require_auth(); // Just need them logged in?

$page_title = "My Awesome Page";
require INCLUDES_PATH . '/header.php';
?>

<!-- Your brilliant content goes here -->

<?php require INCLUDES_PATH . '/footer.php'; ?>
```

### CSRF-Protected Forms
```php
<form method="post" action="<?= htmlspecialchars(url('action/process.php')) ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <!-- Your form fields -->

    <button type="submit">Make It Happen</button>
</form>
```

---

## 🗄️ Database Layout

### Core Tables
- **login** → User accounts and credentials
- **articles** → Your content goldmine
- **categories** → Organizational bliss

### RBAC Tables (The Permission Engine)
- **roles** → Role definitions
- **permissions** → Available permissions
- **role_permissions** → Roles ↔ Permissions connections
- **user_roles** → Users ↔ Roles assignments

---

## 🔄 Migration Guide

Upgrading from the old `products` table version?

```bash
mysql -u root -p blog < database_articles_categories_migration.sql
```

This magical script will:
1. Wave goodbye to the old `products` table
2. Welcome fresh `articles` and `categories` tables
3. Update all permissions from `products.*` to `articles.*`
4. Add shiny category permissions
5. Seed some starter categories

---

## 🔧 Troubleshooting (When Things Go Sideways)

### Common Hiccups & Fixes

**🔐 Permissions acting weird after database changes?**
- Solution: The classic IT fix—logout and login again

**📁 Can't see category management as super admin?**
- Verify super admin role is assigned
- Try the logout-login dance
- Check permission cache

**🔌 Database throwing a tantrum?**
- Confirm `.env` file exists with correct credentials
- Make sure database server is actually running
- Double-check database name matches everywhere

**📝 Forms refusing to submit?**
- CSRF token present? Check!
- JavaScript errors in console? Investigate!

### Pro Developer Tips

- **Local dev mode**: Set `APP_ENV=local` in `.env` to see those helpful errors
- **Debugging**: XAMPP/LAMPP error logs are your friends
- **Session weirdness**: Clear cookies or go incognito

---

## 🎯 Final Words

This project is proof that vanilla PHP can be elegant, secure, and maintainable. Whether you're learning, teaching, or building, this codebase has your back.

**Remember**: 
- Security is not optional
- Clean code is happy code
- Comments are love letters to your future self

Happy coding! 🚀

---

*Built with ❤️ and pure PHP—no frameworks were harmed in the making of this application.*
