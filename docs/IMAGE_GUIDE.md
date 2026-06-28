# Image Guide

The store uses a **consistent set of branded SVG product tiles** (one per product) in
`assets/images/products/`. Each tile shows the category, product name and brand in the
SportZone style, so the catalogue looks clean and uniform out of the box — no external
downloads needed and it works fully offline.

## Replacing a tile with a real photo (optional)
If you'd rather use a real product photo for any item:
1. Save your photo (square, ~600×600px) into `assets/images/products/`.
2. Either give it the **same filename** as the tile it replaces (e.g. `running-shoes.svg` →
   keep using that name but as an image is awkward, so instead) update the product's `image`
   field to your new filename via **Admin → Products → Edit** (the upload handles this
   automatically), or in the database.

The simplest way is **Admin → Products → Edit → Product Image**, which uploads the file and
points the product at it for you.

## Tile filenames
`football-ball.svg`, `football-shin-guards.svg`, `football-gloves.svg`, `football-boots.svg`,
`basketball-ball.svg`, `basketball-shoes.svg`, `basketball-jersey.svg`, `basketball-knee-pads.svg`,
`running-shoes.svg`, `running-socks.svg`, `running-watch.svg`, `running-tights.svg`,
`gym-dumbbells.svg`, `gym-yoga-mat.svg`, `gym-kettlebell.svg`, `gym-bands.svg`,
`sportswear-tshirt.svg`, `sportswear-jacket.svg`, `sportswear-hoodie.svg`, `sportswear-cap.svg`

The home-page hero uses a clean dark banner (no image file required).
