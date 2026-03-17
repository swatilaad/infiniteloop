import { NextResponse } from 'next/server';

/**
 * Debug route — shows raw WordPress API responses.
 * Visit: http://localhost:3000/api/debug-wp
 * DELETE this file before going to production.
 */
export async function GET() {
  const base = process.env.NEXT_PUBLIC_WP_BASE_URL;

  if (!base) {
    return NextResponse.json({ error: 'NEXT_PUBLIC_WP_BASE_URL is not set' }, { status: 500 });
  }

  const results: Record<string, unknown> = { base_url: base };

  // 1. Test basic REST API connectivity
  try {
    const r = await fetch(`${base}/wp-json`, { cache: 'no-store' });
    results.wp_json_status = r.status;
    const json = await r.json() as Record<string, unknown>;
    results.wp_version = json.description ?? json.name ?? 'connected';
  } catch (e) {
    results.wp_json_error = String(e);
  }

  // 2. Fetch pages list (check slugs)
  try {
    const r = await fetch(`${base}/wp-json/wp/v2/pages?per_page=20&_fields=id,slug,title,status`, { cache: 'no-store' });
    results.pages_status = r.status;
    results.pages = await r.json();
  } catch (e) {
    results.pages_error = String(e);
  }

  // 3. Fetch home page with ACF
  try {
    const r = await fetch(`${base}/wp-json/wp/v2/pages?slug=home&_fields=id,slug,title,acf`, { cache: 'no-store' });
    results.home_page_status = r.status;
    results.home_page_raw = await r.json();
  } catch (e) {
    results.home_page_error = String(e);
  }

  // 4. Fetch ACF options
  try {
    const r = await fetch(`${base}/wp-json/acf/v3/options/options`, { cache: 'no-store' });
    results.options_status = r.status;
    results.options_raw = await r.json();
  } catch (e) {
    results.options_error = String(e);
  }

  return NextResponse.json(results, {
    headers: { 'Content-Type': 'application/json' },
  });
}
