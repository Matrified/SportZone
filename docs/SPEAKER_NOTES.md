# SportZone — Full Presentation Script & Speaker Notes
**CIT6224 · Group 16 · Section C — recorded video (10–15 min, MP4)**

Slides: **`docs/SportZone_Presentation.pptx`** (14 slides; the same notes are also in each slide's
Notes pane). Read the lines below roughly word-for-word — they're written to sound natural.

## Presenter plan
| Part | Slides | Presenter |
|------|--------|-----------|
| Intro, business concept, problem, objectives | 1–4 | **Hadi** |
| Architecture, tech stack, database | 5–7 | **Mohamed** |
| Customer features, admin features, design decisions | 8–10 | **Ahmed** |
| Security | 11 | **Mohamed** |
| **Live demo** | 12 | **One presenter (you)** |
| Team contributions, conclusion | 13–14 | **Osman** |

Target ≈13 minutes: slides ≈8 min, demo ≈4–5 min. Make sure all four voices are heard.

---

## SLIDE-BY-SLIDE SCRIPT

### Slide 1 — Title  *(Hadi)*
> "Good day, we are Group 16 from TC2L. Our project is **SportZone**, a sports equipment and
> apparel e-commerce website built with HTML, CSS, JavaScript, PHP and MySQL on XAMPP. Our team is
> Hadi, Ahmed, Osman and Mohamed. I'm Hadi, and I'll begin with the business concept."

### Slide 2 — Executive Summary  *(Hadi)*
> "SportZone is a **business-to-consumer online sports store**. It sells equipment, apparel and
> fitness gear across five categories — football, basketball, running, gym and sportswear. It
> removes the limits of a physical shop: it's open 24/7, shows real-time stock, and keeps
> everything in one place. Customers can browse, search and filter products, save favourites to a
> wishlist, check out, and track their orders — while admins manage the whole catalogue and orders
> from a dashboard."

### Slide 3 — Problem & Target Users  *(Hadi)*
> "We started from the gaps in traditional sports retail — limited stock and shelf space, fixed
> opening hours, and no easy way to compare prices or check availability. Our target users are
> students, athletes, gym-goers, casual players and sports clubs. What they need is convenience,
> clear product information and reviews, fast search, and the ability to track their orders — and
> that's what SportZone delivers."

### Slide 4 — Objectives  *(Hadi)*
> "Our objectives were to build a fully functional B2C platform with secure, role-based logins for
> customers and admins; full product browsing with search, filtering and sorting; a cart, wishlist
> and multi-step checkout with order tracking; admin management tools; and a responsive design that
> works on phone and desktop. I'll now hand over to Mohamed for the technical design."

### Slide 5 — System Architecture  *(Mohamed)*
> "Thanks Hadi. SportZone uses a classic **three-tier architecture**. The browser handles
> presentation with HTML, CSS and vanilla JavaScript; **PHP on Apache** handles the business logic
> and validation; and **MySQL** stores the data. We also use shared PHP includes — header, footer,
> config, database connection and helper functions — so common code is written once and reused on
> every page."

### Slide 6 — Technology Stack & Constraints  *(Mohamed)*
> "The stack is HTML5, hand-written CSS3, vanilla JavaScript, PHP and MySQL, all running on XAMPP.
> A key constraint in the brief was **no UI frameworks** — no Bootstrap or Tailwind — so every bit
> of styling is our own CSS, and even the admin sales chart is drawn manually on an HTML5 canvas
> with JavaScript. For tools we used VS Code, phpMyAdmin and GitHub."

### Slide 7 — Database Design  *(Mohamed)*
> "The database has **ten normalised tables** in third normal form, with foreign keys enforcing
> integrity and a CHECK constraint keeping ratings between one and five. One design decision worth
> highlighting: the order-items table stores the product name and price **at the time of purchase**,
> so past orders stay accurate even if a product is later renamed, repriced or removed. I'll pass to
> Ahmed for the features."

### Slide 8 — Customer Features  *(Ahmed)*
> "Thanks Mohamed. On the customer side we have full product browsing — keyword search, filters by
> category, brand and price, plus sorting and pagination. Each product page shows reviews and star
> ratings, and only customers who actually bought the item can review it. Customers also get a
> wishlist with a one-click heart, a persistent shopping cart, a multi-step checkout that supports
> promo codes, and full order history with tracking and cancellation. You'll see all of this in the
> live demo."

### Slide 9 — Admin Features  *(Ahmed)*
> "On the admin side there's a dashboard with live statistics, a sales chart and low-stock alerts.
> Admins can create, edit and delete products — including image upload — manage orders and update
> their status, manage users and categories, and read messages sent from the contact page. Every
> admin page is role-protected, so only an admin account can reach it."

### Slide 10 — Design Decisions  *(Ahmed)*
> "A few design decisions: we wrote **custom CSS** for full control and to meet the no-framework
> rule; the layout is **mobile-first and responsive**, with a hamburger menu on small screens; we
> used a clean black, white and red theme with prices in Ringgit; and we validate forms twice —
> instantly on the client for good UX, and again on the server for safety. Mohamed will explain how
> we secured the system."

### Slide 11 — Security  *(Mohamed)*
> "Security was built in from the start. Every database query uses **prepared statements** to stop
> SQL injection. All output is **escaped** to prevent cross-site scripting. Passwords are **hashed
> with bcrypt**. Restricted pages are protected by **session and role checks**, and every form that
> changes data carries a **CSRF token**. Now let's move to the live demonstration."

---

## SLIDE 12 — LIVE DEMONSTRATION  *(one presenter)*
Switch to the browser (XAMPP running, database imported). Narrate as you click. ~4–5 minutes.
Logins ready: customer `customer@sportzone.com / customer123`, admin `admin@sportzone.com / admin123`.

> "This is the SportZone home page — a hero banner, the category grid, and featured products."
1. **Home** — scroll slowly through the page.

> "Let me log in as a customer."
2. **Login** → enter the customer email/password → submit. *(Optional: first open Register and type an
   invalid email to show the red real-time validation, then go to Login.)*

> "On the Shop page I can search, and filter by category, brand and price. I'll sort by price, low
> to high."
3. **Shop** → type `shoes` in search → apply a **category** and a **price** filter → change **Sort by →
   Price: Low to High** → mention the pagination at the bottom.

> "Opening a product, I can see details and customer reviews — and notice the *Verified Purchase*
> badge, because only buyers can review. I'll save it to my wishlist, choose a size, and add it to
> the cart."
4. **Product details** → open a product → click the **Reviews** tab (point out the **Verified Purchase**
   badge) → click the **heart** (wishlist) → select a **size** → **Add to Cart**.

> "Here's my wishlist with the saved item."
5. **Wishlist** — open it via the heart icon in the top bar.

> "In the cart I can change quantities — the total updates live — then proceed to checkout."
6. **Cart** → change a quantity (totals update) → **Proceed to Checkout**.

> "Checkout is a three-step form: shipping, payment, then review. I'll apply the promo code
> SPORT10 — you can see the 10% discount applied — and place the order."
7. **Checkout** → fill shipping → choose payment → enter promo **`SPORT10`** → **Apply** (show discount)
   → **Review** → **Place Order**.

> "The order is confirmed. Under *My Orders* I can view the full details, and cancel an order while
> it's still pending — which also returns the stock."
8. **Order success → My Orders** → click **View** (details modal) → **Cancel** a Pending order.

> "Now I'll log in as an admin."
9. **Logout → Login** as admin.

> "The admin dashboard shows total products, customers, orders and revenue, a sales chart, and
> low-stock alerts."
10. **Dashboard** — point out stat cards, the **sales chart**, low-stock list.

> "Admins manage products with full create, edit and delete — including image upload."
11. **Products** → open **Add/Edit** (show the image upload field) → mention Delete.

> "And here I can update an order's status, for example from Pending to Processing."
12. **Orders** → change a **status** in the dropdown.

> "Finally, the site is fully responsive — here it is on a mobile width with the hamburger menu."
13. Narrow the browser (or DevTools device mode) to show the **hamburger menu** and stacked layout.

> "That concludes the demo — I'll hand back for the summary."
14. *(Optional)* finish on the **Our Team** page.

---

### Slide 13 — Team Contributions  *(Osman)*
> "Thanks. To summarise who built what: **Hadi** did authentication and user management — login,
> register and profile. **Ahmed** built the product catalog — the home, listing and product detail
> pages. **I (Osman)** built the cart, checkout and order processing. And **Mohamed** built the admin
> panel — the dashboard, product and order management. Each of us delivered three pages and three
> features."

### Slide 14 — Conclusion & Future Work  *(Osman)*
> "In conclusion, we delivered a complete, secure and responsive e-commerce system that meets all
> the core requirements, plus extra features like the wishlist, promo codes and verified reviews.
> In future we'd add an online payment gateway, email notifications, and product recommendations.
> Thank you for watching — we're happy to take any questions."

---

## RECORDING CHECKLIST
- Start XAMPP (Apache + MySQL) and import `sportzone.sql` + `sample_data.sql` before recording.
- Record with OBS Studio or PowerPoint → *Record* (full screen, 1080p).
- Do one silent practice run; keep the demo to ~4–5 minutes.
- Export as **MP4**, total length **10–15 minutes**.
- Make sure each member speaks (helps the individual marks).
