# SportZone — Recorded Presentation Guide
**CIT6224 · Group 16 · Section C (10 marks)**

## What you must present (from the assignment)
The recorded video (**10–15 minutes**, MP4) must contain three things:
1. **An executive summary** of the e-commerce business concept
2. **Explanation of system features and design decisions**
3. **A live demonstration** of the developed system

So you need a **short slide deck** (parts 1–2) **plus a live screen demo** (part 3).
Section D (Individual Interview) is separate and done in person in Week 14 — not part of this video.

---

## Suggested timing (≈13 minutes)
| Segment | Time | Who |
|---|---|---|
| Intro + executive summary | 1.5 min | Member 1 |
| Problem, users, objectives | 1.5 min | Member 1 |
| Architecture, tech stack, database | 2 min | Member 4 |
| Features & design decisions (slides) | 2 min | Member 2 |
| Security | 1 min | Member 4 |
| Live demo | 4 min | Members 2 & 3 |
| Team contributions + conclusion | 1 min | Member 3 |

Tip: each member should speak so the lecturer hears all four voices (helps individual marks).

---

## SLIDE DECK (build in PowerPoint / Google Slides — ~13 slides)
Keep slides light: a heading + 3–5 bullets. Use the SportZone black/white/red theme.

### Slide 1 — Title
- SportZone — Sports Equipment & Apparel E-Commerce System
- CIT6224 Web Application Development · Group 16 (TC2L)
- Members: Hadi Abdulla, Ahmed Mahmoud Mohamed, Osman Omer Gumaa, Mohamed Tarek
- **Speaker notes:** "Good day, we're Group 16. Our project is SportZone, a sports e-commerce website built with PHP and MySQL."

### Slide 2 — Executive Summary (Business Concept)
- B2C online store for sports equipment, apparel & fitness gear
- Five categories: Football, Basketball, Running, Gym, Sportswear
- Customers browse, search, add to cart/wishlist, checkout & track orders
- Admins manage products, orders, users from a dashboard
- **Notes:** "SportZone is a direct-to-consumer store. It removes the limits of a physical shop — open 24/7, real-time stock, and everything in one place."

### Slide 3 — Problem & Target Users
- Traditional retail: limited stock/space, fixed hours, no price transparency
- Target users: students, athletes, gym-goers, casual players, sports clubs
- Need: convenience, product info & reviews, quick search, order tracking
- **Notes:** "We looked at the gaps in traditional sports retail and designed around real user needs."

### Slide 4 — Objectives
- Fully functional B2C e-commerce platform
- Secure registration & role-based authentication (customer/admin)
- Product browsing with search, filter, sort
- Cart, wishlist, multi-step checkout, order tracking
- Admin tools + responsive design
- **Notes:** keep brief, read the headline objectives.

### Slide 5 — System Architecture
- Three-tier: Browser (HTML/CSS/JS) → PHP on Apache → MySQL
- Reusable PHP includes (header, footer, config, db, functions)
- **Show the architecture diagram** (from the report)
- **Notes:** "A classic three-tier design. Shared PHP includes keep the code DRY."

### Slide 6 — Technology Stack & Constraints
- HTML5, **custom CSS3 (no frameworks)**, vanilla JavaScript
- PHP + MySQL on XAMPP
- Constraint met: no Bootstrap/Tailwind; chart drawn on HTML5 canvas
- **Notes:** "Per the brief, we used no UI frameworks — all CSS is hand-written."

### Slide 7 — Database Design
- 10 normalised tables (3NF), foreign keys, CHECK constraint
- **Show the ER diagram** (from the report)
- Order items store a price snapshot for accurate history
- **Notes:** point to users → orders → order_items → products.

### Slide 8 — Customer Features
- Search, category/brand/price filters, sorting, pagination
- Product details with verified-purchase reviews & ratings
- Wishlist (AJAX heart), persistent cart
- Multi-step checkout with promo codes, order tracking + cancel
- **Notes:** mention these are demoed live next.

### Slide 9 — Admin Features
- Dashboard: stats + live sales chart + low-stock alerts
- Product CRUD with image upload
- Order management (status updates), users, categories, messages
- **Notes:** "Role-protected — only admins can reach these pages."

### Slide 10 — Design Decisions
- Custom CSS for full control + constraint compliance
- Mobile-first responsive layout (hamburger nav, fluid grids)
- Prices in RM; clean black/white/red brand theme
- Real-time client validation + server-side validation
- **Notes:** explain *why* (constraint + UX + maintainability).

### Slide 11 — Security
- SQL injection: prepared statements everywhere
- XSS: output escaping (htmlspecialchars)
- Passwords hashed (bcrypt); sessions + role checks; CSRF tokens
- **Notes:** "Security was built in from the start, not bolted on."

### Slide 12 — Live Demo
- Just a heading: "Live Demonstration" (then switch to the browser)

### Slide 13 — Team Contributions + Conclusion
- Member 1 (Hadi): auth & user management — login, register, profile
- Member 2 (Ahmed): catalog — home, listing, product details
- Member 3 (Osman): cart, checkout, orders
- Member 4 (Mohamed): admin dashboard, product & order management
- Future: payment gateway, email alerts, recommendations
- "Thank you — questions?"

---

## LIVE DEMO SCRIPT (≈4 min, click-by-click)
Start logged out on the home page. Have the customer and admin logins ready.

1. **Home** — scroll through hero, categories, featured products.
2. **Register** — open Register, type a bad email to show the red validation, then register a real account. *(or skip and just log in)*
3. **Login** — log in as `customer@sportzone.com` / `customer123`.
4. **Browse & search** — go to Shop, type "shoes" in search, then apply a **category** and **price filter**, change **Sort by → Price: Low to High**.
5. **Product details** — open a product, show the **reviews tab + Verified Purchase badge**, click the **heart (wishlist)**, pick a size, **Add to Cart**.
6. **Wishlist** — open the wishlist (heart icon) to show the saved item.
7. **Cart** — open cart, change a quantity (totals update), then **Proceed to Checkout**.
8. **Checkout** — fill shipping → choose payment → on the summary type promo **`SPORT10`** and Apply (show 10% off) → **Place Order**.
9. **Order success → My Orders** — open **View** (details modal), then **Cancel** a pending order (mention stock is returned).
10. **Admin** — log out, log in as `admin@sportzone.com` / `admin123`.
11. **Dashboard** — point out the stat cards, **sales chart**, low-stock alerts.
12. **Products** — Add/Edit a product (show image upload), then show Delete.
13. **Orders** — change an order's **status** in the dropdown.
14. **Responsive** — make the browser narrow (or DevTools device mode) to show the **hamburger menu** and stacked layout.
15. **Team page** — finish on the Our Team page.

---

## RECORDING TIPS
- Use OBS Studio or PowerPoint's "Record" — share full screen at 1080p.
- Run XAMPP (Apache + MySQL) and import the database before recording.
- Do a silent practice run once; keep the demo under ~4–5 minutes.
- Export as **MP4**, 10–15 minutes total.

## INDIVIDUAL INTERVIEW (Section D — separate, Week 14)
Not in the video, but be ready to: explain your own pages/features, the database, and how
you prevented SQL injection / XSS. Each member answers at least two questions in person.
