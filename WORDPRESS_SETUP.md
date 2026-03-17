# WordPress + Next.js Headless CMS Setup Guide

## Overview

This project uses **WordPress as a headless CMS** with **ACF Pro** for content modeling,
and **Next.js** for the frontend. Content is fetched via the WordPress REST API.

---

## 1. WordPress Installation

1. Install WordPress on your server (local or remote).
2. Set your WordPress URL as the value of `NEXT_PUBLIC_WP_BASE_URL` in `.env.local`:
   ```
   NEXT_PUBLIC_WP_BASE_URL=https://your-wordpress-site.com
   ```

---

## 2. Required WordPress Plugins

Install and activate all of the following:

| Plugin | Purpose |
|---|---|
| **Advanced Custom Fields PRO** | Flexible content fields and options pages |
| **ACF to REST API** *(optional)* | Exposes ACF fields in the WP REST API (ACF Pro 5.11+ has this built-in) |

> **ACF Pro** is required. The free version does not support Flexible Content fields.

---

## 3. Import ACF Field Groups

1. In your WordPress admin, go to **Custom Fields → Tools**.
2. Under **Import Field Groups**, click **Choose File**.
3. Select `acf-export/infinite-loop-acf.json` from this project.
4. Click **Import File**.

This creates two field groups:
- **Global Settings** — attached to the ACF Options page
- **Page Sections** — attached to all Pages, with flexible content layouts for:
  - Hero
  - What We Do (with services repeater)
  - Testimonials (with carousel repeater)
  - Gallery (with items repeater)
  - Contact (with contact items repeater)

---

## 3b. Auto-seed All Content (Skip Manual Data Entry)

Instead of manually filling in the WordPress backend, use one of the provided seeder plugins to populate **all homepage sections and Global Settings automatically**.

### Option A — Text content only (images assigned manually later)

Use `wordpress-import/infinite-loop-seeder.php`:

1. Create a folder: `/wp-content/plugins/infinite-loop-seeder/`
2. Copy `wordpress-import/infinite-loop-seeder.php` into it.
3. Go to **Plugins → Activate** "Infinite Loop — Data Seeder".
4. Visit any admin page — you'll see a success notice.
5. All text content, nav items, footer, and section structure are now populated.
6. Go to **Pages → Home** and assign images to the image fields.
7. Deactivate and delete the plugin.

### Option B — Text content + images (fully automatic)

Use `wordpress-import/seed-with-images.php`. This uploads all template images from the Next.js `public/template/` folder and assigns them to every image field.

1. Open `wordpress-import/seed-with-images.php` and set `IL_TEMPLATE_IMG_PATH`:
   ```php
   define( 'IL_TEMPLATE_IMG_PATH', '/absolute/path/to/nextjs/public/template/2117_infinite_loop/img' );
   ```
2. Create a folder: `/wp-content/plugins/il-full-seeder/`
3. Copy `wordpress-import/seed-with-images.php` into it.
4. Go to **Plugins → Activate** "Infinite Loop — Full Seeder (with Images)".
5. Visit any admin page — all content including images is seeded in one step.
6. Deactivate and delete the plugin.

> **Both seeders are idempotent** — activating them again will not duplicate content.
> To re-seed from scratch: delete the Home page and run `wp option delete il_data_seeded` (or `il_full_seeded`) via WP-CLI.

---

## 4. Create the Options Page

In `functions.php` of your WordPress theme (or a custom plugin), add:

```php
if ( function_exists( 'acf_add_options_page' ) ) {
    acf_add_options_page( array(
        'page_title' => 'Global Settings',
        'menu_title' => 'Global Settings',
        'menu_slug'  => 'global-settings',
        'capability' => 'edit_posts',
        'redirect'   => false,
    ) );
}
```

Then go to **Global Settings** in your WordPress admin and fill in:
- Site Name
- Site Logo (upload image)
- Navigation Items (repeater: label + link)
- Footer Copyright

---

## 5. Enable ACF Fields in REST API

In your theme's `functions.php`, ensure ACF fields are exposed:

```php
// ACF Pro 5.11+ — enable REST API support for all field groups
add_filter( 'acf/settings/rest_api_format', function() {
    return 'standard';
} );
```

For the **Options page** fields, the endpoint is:
```
GET /wp-json/acf/v3/options/options
```

For **page fields**, they are included automatically under the `acf` key:
```
GET /wp-json/wp/v2/pages?slug=home
```

---

## 6. Create the Home Page in WordPress

1. Go to **Pages → Add New**.
2. Set the title to `Home`.
3. Set the slug to `home`.
4. In the **Page Sections** meta box below the editor, add flexible content layouts in order:
   - **Hero** — fill in title, subtitle, background image
   - **What We Do** — fill in intro + 4 service items
   - **Testimonials** — fill in title, bg image, and add testimonials
   - **Gallery** — fill in title and upload gallery images
   - **Contact** — fill in title, bg image, and contact items
5. Publish the page.

---

## 7. Enable CORS (Required for Frontend Access)

Add this to your theme's `functions.php`:

```php
add_action( 'init', function() {
    header( 'Access-Control-Allow-Origin: http://localhost:3000' );
    header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
    header( 'Access-Control-Allow-Headers: Content-Type, Authorization' );
} );
```

For production, replace `http://localhost:3000` with your Next.js domain.

---

## 8. Configure Next.js

1. Copy `.env.local.example` to `.env.local`:
   ```bash
   cp .env.local.example .env.local
   ```
2. Set `NEXT_PUBLIC_WP_BASE_URL` to your WordPress URL.
3. Start the dev server:
   ```bash
   npm run dev
   ```

---

## 9. Next.js Image Optimization

Add your WordPress domain to `next.config.ts` so `next/image` can optimize remote images:

```ts
// next.config.ts
const nextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'your-wordpress-site.com',
      },
    ],
  },
};

export default nextConfig;
```

---

## 10. API Reference

| Data | Endpoint |
|---|---|
| Global options | `GET /wp-json/acf/v3/options/options` |
| Home page + ACF fields | `GET /wp-json/wp/v2/pages?slug=home` |
| All pages | `GET /wp-json/wp/v2/pages` |
| Contact form submit | `POST /api/contact` (Next.js route) |

---

## 11. Folder Structure

```
.
├── acf-export/
│   └── infinite-loop-acf.json     ← Import this into WordPress ACF
├── app/
│   ├── api/
│   │   └── contact/route.ts       ← Contact form API handler
│   ├── globals.css                 ← Global styles (imports template CSS)
│   ├── layout.tsx                  ← Root layout
│   └── page.tsx                    ← Home page (fetches WP data)
├── components/
│   ├── Navbar.tsx
│   ├── Footer.tsx
│   └── sections/
│       ├── HeroSection.tsx
│       ├── WhatWeDoSection.tsx
│       ├── TestimonialsSection.tsx
│       ├── GallerySection.tsx
│       └── ContactSection.tsx
├── lib/
│   ├── types.ts                    ← TypeScript interfaces
│   └── wordpress.ts                ← API fetching functions
└── public/
    └── template/
        └── 2117_infinite_loop/     ← Original template assets (CSS, JS, images)
```

---

## 12. Fallback Mode

If WordPress is **not connected**, the site automatically uses **static fallback data**
defined in `lib/wordpress.ts` → `getFallbackPageData()`. This means the site will
render with the original template content even before WordPress is set up.

---

## 13. Production Deployment

1. Deploy WordPress to a hosting server (e.g. WP Engine, Kinsta, or any PHP host).
2. Deploy Next.js to Vercel (recommended) or any Node.js host.
3. Set environment variables on your deployment platform.
4. Update CORS headers in WordPress to allow your production Next.js domain.
