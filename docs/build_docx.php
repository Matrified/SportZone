<?php
/* Builds SportZone_Technical_Report.docx with embedded diagram images.
   Run: php -d extension=php_zip.dll docs/build_docx.php  */

$BODY = '';
$IMAGES = [];
$imgCounter = 0;

function esc($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8'); }

function r($text, $opt = []) {
    $rpr = '';
    if (!empty($opt['b']))    $rpr .= '<w:b/>';
    if (!empty($opt['i']))    $rpr .= '<w:i/>';
    if (!empty($opt['sz']))   $rpr .= '<w:sz w:val="' . $opt['sz'] . '"/><w:szCs w:val="' . $opt['sz'] . '"/>';
    if (!empty($opt['color'])) $rpr .= '<w:color w:val="' . $opt['color'] . '"/>';
    if ($rpr) $rpr = '<w:rPr>' . $rpr . '</w:rPr>';
    return '<w:r>' . $rpr . '<w:t xml:space="preserve">' . esc($text) . '</w:t></w:r>';
}
function para($runs, $opt = []) {
    global $BODY;
    $spacing = '<w:spacing w:before="' . ($opt['before'] ?? 40) . '" w:after="' . ($opt['after'] ?? 80) . '"/>';
    $jc = !empty($opt['center']) ? '<w:jc w:val="center"/>' : '';
    $outline = isset($opt['outline']) ? '<w:outlineLvl w:val="' . $opt['outline'] . '"/>' : '';
    $BODY .= '<w:p><w:pPr>' . $spacing . $jc . $outline . '</w:pPr>' . (is_array($runs) ? implode('', $runs) : $runs) . '</w:p>';
}
function h1($t) { para([r($t, ['b' => true, 'sz' => 36, 'color' => '1A1A1A'])], ['before' => 280, 'after' => 120, 'outline' => 0]); }
function h2($t) { para([r($t, ['b' => true, 'sz' => 28, 'color' => 'E63946'])], ['before' => 220, 'after' => 100, 'outline' => 1]); }
function h3($t) { para([r($t, ['b' => true, 'sz' => 24, 'color' => '2B2D42'])], ['before' => 160, 'after' => 80, 'outline' => 2]); }
function p($t)  { para([r($t, ['sz' => 21])]); }
function bullet($t) {
    global $BODY;
    $BODY .= '<w:p><w:pPr><w:ind w:left="360" w:hanging="200"/><w:spacing w:before="20" w:after="20"/></w:pPr>'
           . r('•  ', ['sz' => 21]) . r($t, ['sz' => 21]) . '</w:p>';
}
function caption($t) { para([r($t, ['i' => true, 'sz' => 18, 'color' => '666666'])], ['center' => true, 'after' => 160]); }
function spacer() { global $BODY; $BODY .= '<w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>'; }
function pagebreak() { global $BODY; $BODY .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>'; }

function img($relpath, $maxWidthPx = 560) {
    global $BODY, $IMAGES, $imgCounter;
    $disk = __DIR__ . '/' . $relpath;
    if (!file_exists($disk)) { p('[diagram missing: ' . $relpath . ']'); return; }
    $sz = getimagesize($disk);
    $w = $sz[0]; $h = $sz[1];
    if ($w > $maxWidthPx) { $h = (int) round($h * $maxWidthPx / $w); $w = $maxWidthPx; }
    $cx = $w * 9525; $cy = $h * 9525;
    $imgCounter++;
    $rid = 'rIdImg' . $imgCounter;
    $media = 'image' . $imgCounter . '.png';
    $IMAGES[] = ['rid' => $rid, 'file' => $disk, 'media' => $media];
    $BODY .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="120" w:after="60"/></w:pPr>'
        . '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">'
        . '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/><wp:effectExtent l="0" t="0" r="0" b="0"/>'
        . '<wp:docPr id="' . $imgCounter . '" name="Picture ' . $imgCounter . '"/>'
        . '<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>'
        . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        . '<pic:nvPicPr><pic:cNvPr id="' . $imgCounter . '" name="' . $media . '"/><pic:cNvPicPr/></pic:nvPicPr>'
        . '<pic:blipFill><a:blip r:embed="' . $rid . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
        . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
        . '</pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>';
}

function cellPara($text, $bold = false, $color = null) {
    $rpr = '';
    if ($bold) $rpr .= '<w:b/>';
    if ($color) $rpr .= '<w:color w:val="' . $color . '"/>';
    $rpr .= '<w:sz w:val="18"/>';
    return '<w:p><w:pPr><w:spacing w:before="20" w:after="20"/></w:pPr>'
         . '<w:r><w:rPr>' . $rpr . '</w:rPr><w:t xml:space="preserve">' . esc($text) . '</w:t></w:r></w:p>';
}
function tbl($headers, $rows) {
    global $BODY;
    $grid = str_repeat('<w:gridCol/>', count($headers));
    $head = '<w:tr>';
    foreach ($headers as $h) $head .= '<w:tc><w:tcPr><w:shd w:val="clear" w:fill="2B2D42"/></w:tcPr>' . cellPara($h, true, 'FFFFFF') . '</w:tc>';
    $head .= '</w:tr>';
    $rx = '';
    foreach ($rows as $row) {
        $rx .= '<w:tr>';
        foreach ($row as $c) $rx .= '<w:tc>' . cellPara((string) $c) . '</w:tc>';
        $rx .= '</w:tr>';
    }
    $b = '<w:tblBorders><w:top w:val="single" w:sz="4" w:color="AAAAAA"/><w:left w:val="single" w:sz="4" w:color="AAAAAA"/>'
       . '<w:bottom w:val="single" w:sz="4" w:color="AAAAAA"/><w:right w:val="single" w:sz="4" w:color="AAAAAA"/>'
       . '<w:insideH w:val="single" w:sz="4" w:color="CCCCCC"/><w:insideV w:val="single" w:sz="4" w:color="CCCCCC"/></w:tblBorders>';
    $BODY .= '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/>' . $b . '</w:tblPr><w:tblGrid>' . $grid . '</w:tblGrid>' . $head . $rx . '</w:tbl>';
    spacer();
}

// ---------- COVER ----------
for ($i = 0; $i < 3; $i++) spacer();
para([r('MULTIMEDIA UNIVERSITY', ['b' => true, 'sz' => 40, 'color' => '2B2D42'])], ['center' => true, 'after' => 120]);
para([r('Web Application Development (CIT6224)', ['b' => true, 'sz' => 26])], ['center' => true, 'after' => 60]);
para([r('System Development & Technical Report (Section B)', ['sz' => 22])], ['center' => true, 'after' => 240]);
para([r('SPORTZONE', ['b' => true, 'sz' => 64, 'color' => 'E63946'])], ['center' => true, 'after' => 60]);
para([r('Sports Equipment & Apparel E-Commerce System', ['sz' => 24])], ['center' => true, 'after' => 240]);
para([r('Group 16  —  TC2L', ['b' => true, 'sz' => 24])], ['center' => true, 'after' => 160]);
tbl(['Student Name', 'Student ID', 'Role'], [
    ['Hadi Abdulla', '242UC243PP', 'Authentication & User Management Specialist'],
    ['Ahmed Mahmoud Mohamed', '243UC245XT', 'Product Catalog & Display Specialist'],
    ['Osman Omer Gumaa', '243UC245R0', 'Shopping Cart & Order Processing Specialist'],
    ['Mohamed Tarek', '242UC2435F', 'Admin Panel & Management Specialist'],
]);
para([r('Submission (Week 12): 21/06/2026', ['b' => true, 'sz' => 22])], ['center' => true, 'before' => 160]);
pagebreak();

// ---------- TOC ----------
h1('Table of Contents');
foreach ([
    '1.  Introduction and Project Background',
    '2.  Development Methodology',
    '3.  System Architecture and Technologies Used',
    '4.  Database Design (ERD, Relationships, Normalization)',
    '5.  System Design and Implementation Details',
    '6.  System Diagrams (Use Case & Data Flow)',
    '7.  Feature Explanations (with Screenshots)',
    '8.  Security Implementation',
    '9.  Testing',
    '10. User Guide',
    '11. Conclusion and Future Enhancements',
    '12. References',
] as $t) bullet($t);
pagebreak();

// ---------- 1 ----------
h1('1. Introduction and Project Background');
h3('1.1 Background');
p('In the modern digital economy, e-commerce platforms are fundamental to business scalability and convenience. Traditional sports retail is limited by store opening hours, shelf space, lack of real-time stock visibility and geographic reach. SportZone was built to address these gaps by providing an intuitive, secure and complete online sports retail platform.');
h3('1.2 Project Overview');
p('SportZone is a Business-to-Consumer (B2C) online store selling sports equipment, apparel and fitness accessories across five categories: Football, Basketball, Running, Gym and Sportswear. Customers can browse and search products, read and write reviews, save items to a wishlist, manage a shopping cart, apply promo codes and place orders through a multi-step checkout. Administrators manage the catalogue, orders, users and customer messages through a role-protected admin panel. Prices are shown in Malaysian Ringgit (RM).');
h3('1.3 Objectives');
bullet('Develop a fully functional e-commerce platform for sports products.');
bullet('Implement secure user registration and role-based authentication (Customer and Admin).');
bullet('Provide intuitive product browsing with search, filtering and sorting.');
bullet('Enable a smooth shopping cart, wishlist and multi-step checkout experience.');
bullet('Provide administrative tools for managing products, orders, users and messages.');
bullet('Ensure a responsive design across desktop, tablet and mobile devices.');
h3('1.4 Target Users');
p('University and school students, amateur and professional athletes, fitness enthusiasts, casual participants, and sports teams or clubs who want convenient online access to sports gear.');
pagebreak();

// ---------- 2 ----------
h1('2. Development Methodology');
p('The team followed the Agile methodology, delivering the system incrementally across six sprints. Agile was chosen for its iterative nature, flexibility, support for parallel teamwork, and good fit with the assignment milestones (Week 6 proposal, Week 12 final submission).');
tbl(['Sprint', 'Duration', 'Deliverable'], [
    ['Sprint 1', 'Weeks 1-2', 'Project proposal, wireframes, team formation'],
    ['Sprint 2', 'Weeks 3-4', 'Database design (ERD), initial frontend templates'],
    ['Sprint 3', 'Weeks 5-7', 'Core frontend development (HTML/CSS pages)'],
    ['Sprint 4', 'Weeks 8-9', 'Backend development (PHP logic, database integration)'],
    ['Sprint 5', 'Weeks 10-11', 'Integration, extra features, security, testing'],
    ['Sprint 6', 'Week 12', 'Documentation, video presentation, final submission'],
]);
p('Collaboration used GitHub for version control, a shared task list, and a group chat. Shared, reusable PHP includes (config, database connection, header, footer and helper functions) kept the code consistent and reduced merge conflicts.');
pagebreak();

// ---------- 3 ----------
h1('3. System Architecture and Technologies Used');
h3('3.1 Three-Tier Architecture');
p('SportZone follows a classic three-tier architecture: a presentation tier (HTML/CSS/JavaScript) in the browser, an application tier (PHP on Apache) on the server, and a data tier (MySQL).');
img('diagrams/architecture.png');
caption('Figure 3.1 - Three-tier system architecture');
h3('3.2 Technology Stack');
tbl(['Layer', 'Technology', 'Purpose'], [
    ['Markup', 'HTML5', 'Semantic page structure'],
    ['Styling', 'CSS3 (custom, no frameworks)', 'Visual design, responsive layout (Flexbox/Grid)'],
    ['Scripting', 'Vanilla JavaScript', 'Validation, interactivity, AJAX, canvas chart'],
    ['Server', 'PHP', 'Business logic, authentication, database operations'],
    ['Database', 'MySQL', 'Persistent data storage'],
    ['Environment', 'XAMPP (Apache + MySQL)', 'Local development and hosting on Windows'],
]);
p('Constraint compliance: no UI frameworks (Bootstrap, Tailwind, Foundation, Bulma) and no JavaScript libraries were used. The admin sales chart is drawn manually on an HTML5 canvas element with custom JavaScript.');
h3('3.3 Reusable Server-Side Includes');
bullet('config.php - session start, site constants, database credentials.');
bullet('db_connect.php - mysqli connection (UTF-8).');
bullet('functions.php - helpers: sanitize(), auth checks, CSRF, flash messages, money(), cart/wishlist counts, ratings, purchase checks.');
bullet('head.php / header.php / footer.php - shared head, navbar/search/category menu, footer.');
bullet('admin/includes/admin_header.php & admin_footer.php - the admin layout (sidebar + topbar).');
pagebreak();

// ---------- 4 ----------
h1('4. Database Design');
h3('4.1 Tables');
p('The database sportzone_db has ten tables:');
tbl(['Table', 'Purpose', 'Key columns'], [
    ['users', 'Customers and admins (role-based)', 'role, status'],
    ['categories', 'Product categories', 'slug, icon'],
    ['products', 'Product catalogue', 'category_id (FK), price, stock'],
    ['reviews', 'Customer ratings & comments', 'rating (1-5), product_id, user_id'],
    ['cart', 'Persistent per-user cart', 'user_id, product_id, size, quantity'],
    ['wishlist', 'Saved/favourite products', 'user_id, product_id (unique pair)'],
    ['orders', 'Order header, totals, discount, status', 'status, payment_method, total'],
    ['order_items', 'Line items per order (price snapshot)', 'order_id, product_id, price'],
    ['promo_codes', 'Discount codes used at checkout', 'code, type, value, active'],
    ['contact_messages', 'Messages from the Contact page', 'name, email, message'],
]);
h3('4.2 Entity Relationship Diagram');
img('diagrams/erd.png', 600);
caption('Figure 4.1 - Entity Relationship Diagram (sportzone_db)');
h3('4.3 Relationships');
bullet('One category has many products.');
bullet('One user places many orders; one order has many order_items; each order_item refers to one product.');
bullet('One user writes many reviews; one product has many reviews.');
bullet('One user has many cart and wishlist rows; each refers to one product.');
bullet('A promo code (optional) is applied to an order and stored as discount_code + discount_amount.');
h3('4.4 Normalization (3NF)');
bullet('1NF: all columns are atomic and every table has a primary key.');
bullet('2NF: no partial dependencies; non-key attributes depend on the whole primary key.');
bullet('3NF: no transitive dependencies; product details live in products, category details in categories, orders reference users by user_id.');
p('Deliberate denormalization: order_items stores product_name and price at purchase time, so historical orders stay accurate even if a product is later renamed, repriced or deleted (the foreign key uses ON DELETE SET NULL).');
h3('4.5 Referential Integrity');
bullet('Deleting a category cascades to its products; deleting a user cascades to their cart, wishlist, reviews and orders.');
bullet('Deleting a product sets order_items.product_id to NULL so order history is preserved.');
bullet('A CHECK constraint restricts reviews.rating to 1-5.');
pagebreak();

// ---------- 5 ----------
h1('5. System Design and Implementation Details');
p('Work was divided so each member owns three pages and three core features for individual assessment.');
h3('5.1 Member 1 - Hadi Abdulla - Authentication & User Management');
p('Pages: login.php, register.php, profile.php.');
bullet('Registration validates on the server and checks email uniqueness with a prepared statement; passwords are hashed with password_hash(). JavaScript adds real-time feedback.');
bullet('Login verifies credentials with password_verify(), checks status, regenerates the session ID and redirects by role.');
bullet('Profile lets the user edit details and change password (re-checking the current password first).');
h3('5.2 Member 2 - Ahmed Mahmoud Mohamed - Product Catalog & Display');
p('Pages: index.php, products.php, product-details.php.');
bullet('Listing supports keyword search, category/brand/price filters, sorting (newest, best selling, top rated, price, name) and pagination - all via a parameterised SQL query.');
bullet('Product details shows the image, average rating, stock, description/review tabs and a related-products row.');
bullet('Reviews are verified-purchase only and once-per-product; buyers show a "Verified Purchase" badge.');
h3('5.3 Member 3 - Osman Omer Gumaa - Shopping Cart & Order Processing');
p('Pages: cart.php, checkout.php, orders.php.');
bullet('Cart is stored per user with live totals and stock clamping.');
bullet('Checkout is a multi-step form with client/server validation and promo-code support (validated server-side). Order creation runs in a transaction that inserts the order and items, decrements stock and clears the cart.');
bullet('Order tracking lists orders with status badges and a details modal, and allows cancelling a Pending order (which restores stock).');
h3('5.4 Member 4 - Mohamed Tarek - Admin Panel & Management');
p('Pages: admin/dashboard.php, admin/products.php (+ product-form.php), admin/orders.php.');
bullet('Dashboard shows totals, a custom canvas sales chart, recent orders and low-stock alerts.');
bullet('Product CRUD with secure image upload (MIME-checked, size-limited, randomised filename) and live preview.');
bullet('Order management lists/filters orders and updates status; category, user and message modules complete the panel. All admin pages use require_admin().');
pagebreak();

// ---------- 6 ----------
h1('6. System Diagrams');
h3('6.1 Use Case Diagram');
p('Two actors interact with the system: the Customer and the Admin.');
img('diagrams/usecase.png', 600);
caption('Figure 6.1 - Use case diagram');
h3('6.2 Context Diagram (DFD Level 0)');
img('diagrams/dfd0.png', 560);
caption('Figure 6.2 - Context diagram (Level 0 DFD)');
h3('6.3 Data Flow Diagram (Level 1)');
img('diagrams/dfd1.png', 560);
caption('Figure 6.3 - Level 1 data flow diagram');
pagebreak();

// ---------- 7 ----------
h1('7. Feature Explanations (with Screenshots)');
p('Screenshots of the running system are inserted below each heading.');
foreach ([
    ['7.1 Home Page', 'Hero banner, category grid and featured products.'],
    ['7.2 Product Listing', 'Search, category/brand/price filters, sort dropdown and pagination.'],
    ['7.3 Product Details & Reviews', 'Image, stock, tabs, verified-purchase reviews and wishlist button.'],
    ['7.4 Registration & Login', 'Client- and server-side validation with feedback.'],
    ['7.5 Wishlist', 'Saved products with heart toggle.'],
    ['7.6 Shopping Cart', 'Editable quantities, remove, live totals.'],
    ['7.7 Checkout + Promo Code', 'Multi-step checkout with a working promo code (e.g. SPORT10).'],
    ['7.8 Order Confirmation & Tracking', 'Success page and order history with cancel option.'],
    ['7.9 Admin Dashboard', 'Statistics cards, sales chart, recent orders, low-stock alerts.'],
    ['7.10 Admin Product CRUD & Orders', 'Product management and order status updates.'],
    ['7.11 About & Contact', 'About page and Contact form (saved to the database).'],
    ['7.12 Responsive Design', 'Mobile view (hamburger nav, stacked layout, filter drawer).'],
] as $s) { h3($s[0]); p($s[1]); para([r('[ Screenshot to be inserted ]', ['i' => true, 'sz' => 18, 'color' => '999999'])]); }
pagebreak();

// ---------- 8 ----------
h1('8. Security Implementation');
tbl(['Threat', 'Mitigation in SportZone'], [
    ['SQL Injection', 'All queries use mysqli prepared statements with bound parameters; only whitelisted sort keys are placed in SQL.'],
    ['Cross-Site Scripting (XSS)', 'All dynamic output is escaped with htmlspecialchars (sanitize()); JSON in data- attributes is HTML-encoded then parsed.'],
    ['Password security', 'Passwords stored as bcrypt hashes via password_hash(); verified with password_verify().'],
    ['CSRF', 'Every state-changing form carries a per-session token validated with hash_equals().'],
    ['Session fixation', 'session_regenerate_id(true) is called on login.'],
    ['Broken access control', 'require_login() / require_admin() protect pages; admin status re-checked each request.'],
    ['Unsafe file upload', 'Image uploads are MIME-checked, size-limited (3 MB) and stored with randomised filenames.'],
    ['Data integrity', 'Order placement and cancellation use transactions; foreign keys and a CHECK constraint enforce valid data.'],
]);
pagebreak();

// ---------- 9 ----------
h1('9. Testing');
p('A mix of manual functional testing and input/boundary testing was carried out.');
tbl(['#', 'Test case', 'Expected result', 'Status'], [
    ['1', 'Register with mismatched passwords', 'Inline error, no account created', 'Pass'],
    ['2', 'Register with an existing email', 'Email already exists error', 'Pass'],
    ['3', 'Login with wrong password', 'Invalid email or password', 'Pass'],
    ['4', 'Login as admin', 'Redirect to admin dashboard', 'Pass'],
    ['5', 'Add product to cart', 'Cart badge increments, item shown', 'Pass'],
    ['6', 'Update cart quantity above stock', 'Quantity clamped to stock', 'Pass'],
    ['7', 'Apply valid promo code (SPORT10)', 'Discount applied, total updates', 'Pass'],
    ['8', 'Apply invalid promo code', 'Error message, no discount', 'Pass'],
    ['9', 'Place order', 'Order created, stock reduced, cart cleared', 'Pass'],
    ['10', 'Cancel a pending order', 'Status Cancelled, stock restored', 'Pass'],
    ['11', 'Review a product not purchased', 'Blocked with message', 'Pass'],
    ['12', 'Add / remove wishlist item', 'Heart toggles, count updates', 'Pass'],
    ['13', 'Access admin page as customer', 'Redirected to home', 'Pass'],
    ['14', 'SQL injection in search', 'Treated as literal text, no breach', 'Pass'],
    ['15', 'XSS in review field', 'Rendered as escaped text', 'Pass'],
    ['16', 'Submit contact form', 'Saved to database, success message', 'Pass'],
    ['17', 'Responsive at 375 / 768 / 1280 px', 'Layouts adapt correctly', 'Pass'],
]);
pagebreak();

// ---------- 10 ----------
h1('10. User Guide');
h3('10.1 Registration & Login');
bullet('Click the account icon, choose Register, fill in the form and submit. Then log in with your email and password.');
h3('10.2 Browsing & Searching');
bullet('Use the category menu, search bar or Shop page. Apply filters, sort and pagination, then open a product for details and reviews.');
h3('10.3 Wishlist');
bullet('Click the heart on a product (or Add to Wishlist on the product page) to save it; view saved items from the heart icon in the top bar.');
h3('10.4 Placing an Order');
bullet('Add items to the cart, open the cart and Proceed to Checkout. Optionally enter a promo code (e.g. SPORT10). Complete Shipping, Payment and Review, then Place Order.');
h3('10.5 After Ordering');
bullet('Track status under My Orders, view details, or cancel an order while it is still Pending.');
h3('10.6 Admin');
bullet('Log in as admin to reach the dashboard, then manage products, categories, orders, users and contact messages.');
h3('10.7 Login Credentials');
tbl(['Role', 'Email', 'Password'], [
    ['Admin', 'admin@sportzone.com', 'admin123'],
    ['Customer', 'customer@sportzone.com', 'customer123'],
]);
h3('10.8 Promo Codes (demo)');
tbl(['Code', 'Discount'], [['SPORT10', '10% off'], ['SAVE20', '20% off'], ['WELCOME5', 'RM 5 off']]);
pagebreak();

// ---------- 11 ----------
h1('11. Conclusion and Future Enhancements');
h3('11.1 Conclusion');
p('SportZone delivers a complete, secure and responsive B2C e-commerce platform that meets all core requirements: product management, role-based authentication, shopping cart, wishlist, promo codes, order processing and an administrative back office. It demonstrates clean separation of concerns through reusable PHP includes, a normalised MySQL schema, and defensive security practices.');
h3('11.2 Future Enhancements');
bullet('Online payment gateway integration (e.g. Stripe / PayPal) replacing the simulated card step.');
bullet('Email notifications for order confirmation and status changes.');
bullet('Product recommendations based on purchase history.');
bullet('Multiple images per product with a thumbnail gallery.');
bullet('Admin sales reports with CSV export and date-range analytics.');
pagebreak();

// ---------- 12 ----------
h1('12. References');
bullet('PHP Manual - Prepared Statements (mysqli). https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php');
bullet('PHP Manual - password_hash(). https://www.php.net/manual/en/function.password-hash.php');
bullet('OWASP - SQL Injection Prevention Cheat Sheet. https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html');
bullet('OWASP - Cross Site Scripting Prevention Cheat Sheet. https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html');
bullet('MDN Web Docs - HTML, CSS, JavaScript & Canvas API. https://developer.mozilla.org/');
bullet('MySQL 8.0 Reference Manual. https://dev.mysql.com/doc/refman/8.0/en/');
bullet('Apache Friends - XAMPP. https://www.apachefriends.org/');
p('Product images are Creative Commons works sourced via Openverse; full per-image attribution is provided in docs/IMAGE_CREDITS.md. Diagrams were produced with Mermaid. Content from external sources was paraphrased and adapted for this report.');

// =====================================================================
// ASSEMBLE THE DOCX
// =====================================================================
$ns = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
    . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
    . 'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
    . 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
    . 'xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"';

$documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<w:document ' . $ns . '><w:body>' . $BODY
    . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>'
    . '</w:body></w:document>';

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Default Extension="png" ContentType="image/png"/>'
    . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    . '</Types>';

$rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    . '</Relationships>';

$docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
foreach ($IMAGES as $im) {
    $docRels .= '<Relationship Id="' . $im['rid'] . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . $im['media'] . '"/>';
}
$docRels .= '</Relationships>';

$outFile = __DIR__ . '/SportZone_Technical_Report.docx';
if (file_exists($outFile)) unlink($outFile);

$zip = new ZipArchive();
if ($zip->open($outFile, ZipArchive::CREATE) !== true) { fwrite(STDERR, "cannot create docx\n"); exit(1); }
$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rootRels);
$zip->addFromString('word/document.xml', $documentXml);
$zip->addFromString('word/_rels/document.xml.rels', $docRels);
foreach ($IMAGES as $im) {
    $zip->addFile($im['file'], 'word/media/' . $im['media']);
}
$zip->close();

echo "Created: $outFile (" . filesize($outFile) . " bytes, " . count($IMAGES) . " images embedded)\n";
