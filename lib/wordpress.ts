import type {
  ACFImage,
  GlobalOptions,
  PageData,
  PageSection,
  WPImage,
  WPRestPage,
} from './types';

const WP_BASE_URL = process.env.NEXT_PUBLIC_WP_BASE_URL;
const WP_API = `${WP_BASE_URL}/wp-json`;

// ─── Helpers ─────────────────────────────────────────────────────────────────

async function wpFetch<T>(endpoint: string): Promise<T> {
  const url = `${WP_API}${endpoint}`;
  const res = await fetch(url, {
    next: { revalidate: 60 },
    headers: { 'Content-Type': 'application/json' },
  });

  if (!res.ok) {
    throw new Error(`WordPress API ${res.status}: ${url}`);
  }

  return res.json() as Promise<T>;
}

// Convert an ACF image object (or any falsy value) into the WPImage shape
// ACF returns the full attachment object: { ID, id, url, alt, width, height, sizes, … }
function normalizeImage(raw: ACFImage | WPImage | null | undefined | false | ''): WPImage | null {
  if (!raw || typeof raw !== 'object') return null;
  const img = raw as ACFImage;
  if (!img.url) return null;
  return {
    url: img.url,
    alt: img.alt || '',
    width: img.width,
    height: img.height,
  };
}

// Walk a section and replace every ACF image object with a plain WPImage
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function normalizeSection(section: Record<string, any>): PageSection {
  const IMAGE_KEYS = [
    'hero_bg_image',
    'bg_image',
    'bg_image_mobile',
    'image',
    'thumbnail',
    'full_image',
    'site_logo',
    'favicon',
  ];

  const result: Record<string, unknown> = {};

  for (const [key, value] of Object.entries(section)) {
    if (IMAGE_KEYS.includes(key)) {
      result[key] = normalizeImage(value as ACFImage) ?? value;
    } else if (Array.isArray(value)) {
      // Recurse into repeater / flexible content sub-arrays
      result[key] = value.map((item) =>
        item && typeof item === 'object' ? normalizeSection(item) : item
      );
    } else {
      result[key] = value;
    }
  }

  return result as unknown as PageSection;
}

// ─── Global Options ───────────────────────────────────────────────────────────

export async function getGlobalOptions(): Promise<GlobalOptions> {
  // Attempt 1: ACF to REST API plugin endpoint
  try {
    const data = await wpFetch<{ acf: GlobalOptions }>('/acf/v3/options/options');
    if (data?.acf && typeof data.acf === 'object' && !Array.isArray(data.acf)) {
      return normalizeOptions(data.acf);
    }
  } catch {
    // not available — try next
  }

  // Attempt 2: Custom REST endpoint (functions-snippet.php)
  const data = await wpFetch<GlobalOptions>('/il/v1/options');
  return normalizeOptions(data);
}

// ─── Home Page ────────────────────────────────────────────────────────────────

export async function getHomePage(): Promise<PageData> {
  const pages = await wpFetch<WPRestPage[]>('/wp/v2/pages?slug=home');

  if (!pages || pages.length === 0) {
    throw new Error('No page with slug "home" found in WordPress.');
  }

  const page = pages[0];
  const acf = page.acf;

  if (!acf || Array.isArray(acf) || !acf.page_sections) {
    throw new Error(
      'ACF fields are empty on the home page. ' +
      'In WordPress: Custom Fields → edit each field group → enable "Show in REST API" → Save.'
    );
  }

  const sections = (acf.page_sections as unknown as Record<string, unknown>[]).map(
    (s) => normalizeSection(s)
  );

  return {
    id: page.id,
    slug: page.slug,
    title: page.title.rendered,
    seo_title: acf.seo_title,
    seo_description: acf.seo_description,
    sections,
  };
}

// ─── Normalise options response ───────────────────────────────────────────────

function normalizeOptions(raw: GlobalOptions): GlobalOptions {
  return {
    site_name: raw.site_name || '',
    site_logo: normalizeImage(raw.site_logo as ACFImage | null),
    favicon: normalizeImage(raw.favicon as ACFImage | null),
    nav_items: Array.isArray(raw.nav_items) ? raw.nav_items : [],
    footer_copyright: raw.footer_copyright || '',
  };
}
