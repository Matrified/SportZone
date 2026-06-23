# Icon Guide — which icon image to put where

The site currently shows **emoji** as a fallback for every icon. As soon as you drop a
PNG with the **exact filename** into `assets/images/icons/`, it replaces the emoji
automatically (no code change needed).

- Folder: **`assets/images/icons/`**
- Format: **PNG with a transparent background**
- Recommended size: UI icons **48×48px**, category icons **64×64px**
- Colour: use **dark / black** icons. They sit on a white header. The **search** icon is
  automatically inverted to white by CSS (because the search button is dark), so a dark
  search icon is fine too.
- Free sources: [flaticon.com](https://www.flaticon.com), [icons8.com](https://icons8.com),
  [thenounproject.com](https://thenounproject.com) (check the licence / give attribution).

## Header / interface icons
| Filename | Where it shows | What to use |
|---|---|---|
| `search.png` | Search button in the top bar | Magnifying glass |
| `cart.png` | Cart link (top right) | Shopping cart or bag |
| `heart.png` | Wishlist link (top right) | Heart outline |
| `user.png` | Account / Login link | Person / user |
| `logout.png` | Logout link | Exit / logout arrow |

## Home page category icons
| Filename | Category card | What to use |
|---|---|---|
| `cat-football.png` | Football | Football (soccer ball) |
| `cat-basketball.png` | Basketball | Basketball |
| `cat-running.png` | Running | Running shoe or runner |
| `cat-gym.png` | Gym | Dumbbell |
| `cat-sportswear.png` | Sportswear | T-shirt |
| `cat-new.png` | New Arrivals | Sparkle / star / "new" badge |

## Notes
- The small heart on each product card and the "Add to Wishlist" button use a text heart
  (♥ / ♡) styled with CSS — they don't need an image.
- The hamburger menu (mobile) uses a text "≡" — no image needed, but you can style it.
- The **admin panel** icons (📊 📦 🏷 🧾 👥 💰) are emoji and are internal-only; replacing them
  is optional. If you want to, tell me and I'll wire them up the same way.
