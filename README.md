# SportZone E-Commerce System
**CIT6224 - Web Application Development | Group 16 (TC2L)**

A fully functional sports equipment, apparel & fitness accessories online store built with
**HTML5, CSS3, vanilla JavaScript, PHP and MySQL** on **XAMPP**. No UI frameworks were used —
all styling is custom CSS, per the assignment constraints.

---

## ✅ Features Implemented

**Customer storefront**
- Home page with dynamic categories + featured products
- Product listing with **search, category / brand / price filtering, sorting & pagination**
  (sort by newest, best selling, top rated, price, name)
- Product details with image, **star ratings & customer reviews**, related products
- **Wishlist** — save favourite products (heart) and view them on a wishlist page
- Persistent **shopping cart** (add / update quantity / remove / clear)
- Multi-step **checkout** (shipping → payment → review) with order placement
- **Order history & tracking** with details modal
- Secure **registration, login, logout**, profile editing & password change
- Group / team details page
- Prices shown in **RM (Malaysian Ringgit)**

**Admin panel (role-protected)**
- Dashboard with live statistics + **custom canvas sales chart** + low-stock alerts
- **Product management (full CRUD)** with image upload & preview
- Category management (add / delete)
- Order management with status updates (Pending → Processing → Delivered → Cancelled)
- User management (view, activate / deactivate customers)

**Engineering**
- Reusable PHP includes (config, DB, header, footer, helper functions)
- **Prepared statements everywhere** (SQL-injection safe)
- Output escaping with `htmlspecialchars` (**XSS safe**)
- `password_hash()` / `password_verify()` for credentials
- **CSRF tokens** on all state-changing forms
- Session-based authentication & role detection
- Fully **responsive** (mobile / tablet / desktop) custom CSS

---

## 🔧 Setup Instructions (XAMPP — Windows)

1. **Copy the project** into your XAMPP `htdocs` directory:
   `C:\xampp\htdocs\SportZone`
   > The app expects the folder to be named **`SportZone`** (the URL base is `/SportZone/`).
   > If you use a different folder name, change `BASE_URL` in `includes/config.php`.

2. **Start Apache & MySQL** from the XAMPP Control Panel.

3. **Create the database**
   - Open `http://localhost/phpmyadmin`
   - Click **Import → Choose File →** `database/sportzone.sql` → **Go**
   - This creates `sportzone_db` with all tables, 40 sample products,
     and the **admin + customer accounts** (passwords already hashed).

4. *(Optional — recommended for the demo)* **Add sample orders & reviews**
   - Import `database/sample_data.sql` the same way.
   - This populates the dashboard analytics, sales chart and order history
     so they look complete in screenshots / the video.

5. **Open the site:** `http://localhost/SportZone/index.php`

6. **(Optional) Add product images** — see `docs/IMAGE_GUIDE.md` for the exact
   filenames and what photo to use for each. The store works fine with the
   built-in placeholder until then.

### Login Credentials
| Role     | Email                    | Password     |
|----------|--------------------------|--------------|
| Admin    | admin@sportzone.com      | admin123     |
| Customer | customer@sportzone.com   | customer123  |

---

## 📁 Folder Structure
```
SportZone/
├── admin/                       # Admin-only pages (role-protected)
│   ├── includes/                #   admin_header.php, admin_footer.php
│   ├── dashboard.php            #   analytics + sales chart
│   ├── products.php             #   product list
│   ├── product-form.php         #   add / edit product (image upload)
│   ├── product-delete.php       #   delete handler
│   ├── categories.php           #   category CRUD
│   ├── orders.php               #   order list + status update
│   ├── order-details.php        #   single order view
│   └── users.php                #   user management
├── assets/
│   ├── css/  style.css, admin.css
│   ├── js/   main.js, auth.js, shop.js, product.js, checkout.js, orders.js, admin.js
│   └── images/  placeholder.svg + products/ (10 themed product images)
├── database/
│   ├── sportzone.sql            # schema + products + categories + accounts
│   └── sample_data.sql          # optional: sample orders & reviews for the demo
├── includes/                    # config, db_connect, functions, head, header, footer
├── docs/                        # technical report, image guide, screenshots
├── index.php                    # Home
├── products.php                 # Product listing
├── product-details.php          # Product details + reviews
├── wishlist.php / toggle_wishlist.php   # Wishlist page + add/remove handler
├── cart.php / add_to_cart.php / update_cart.php
├── checkout.php / place_order.php / order-success.php
├── login.php / register.php / logout.php / profile.php / orders.php
├── group.php                    # Group member details page
└── README.md
```

## 🖼️ Product Images
All images on the site start as **placeholders**. See **`docs/IMAGE_GUIDE.md`** for the exact
filename and a suggested photo for each product (and the home banner). Drop the files into
`assets/images/products/` (or upload via **Admin → Products → Edit**) and they appear instantly.

## 👥 Team & Roles (Group 16)
| Name | Student ID | Role | Pages | Features |
|------|-----------|------|-------|----------|
| Hadi Abdulla | 242UC243PP | Authentication & User Management | login, register, profile | registration, login/session, profile mgmt |
| Ahmed Mahmoud Mohamed | 243UC245XT | Product Catalog & Display | home, products, product-details | browsing/categorization, search & filter, reviews |
| Osman Omer Gumaa | 243UC245R0 | Shopping Cart & Order Processing | cart, checkout, orders | cart system, checkout/order placement, order tracking |
| Mohamed Tarek | 242UC2435F | Admin Panel & Management | dashboard, products(CRUD), orders | dashboard analytics, product CRUD, order management |
