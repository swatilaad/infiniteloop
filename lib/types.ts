// ─── Image ───────────────────────────────────────────────────────────────────

// Normalised shape used throughout all components
export interface WPImage {
  url: string;
  alt: string;
  width?: number;
  height?: number;
}

// Raw shape ACF returns for image fields (return_format: "array")
export interface ACFImage {
  ID: number;
  id: number;
  url: string;
  alt: string;
  width: number;
  height: number;
  sizes?: Record<string, string | number>;
  [key: string]: unknown;
}

// ─── Global Options (ACF Options Page) ───────────────────────────────────────

export interface NavItem {
  label: string;
  link: string;
}

export interface GlobalOptions {
  site_name: string;
  site_logo: WPImage | null;
  favicon: WPImage | null;
  nav_items: NavItem[];
  footer_copyright: string;
}

// ─── Section: Hero ───────────────────────────────────────────────────────────

export interface HeroSectionData {
  acf_fc_layout: 'hero';
  hero_title: string;
  hero_subtitle: string;
  hero_bg_image: WPImage;
  hero_arrow_target: string; // e.g. "#whatwedo"
}

// ─── Section: What We Do ─────────────────────────────────────────────────────

export interface ServiceItem {
  icon_class: string; // e.g. "far fa-3x fa-chart-bar"
  title: string;
  description: string;
  button_text: string;
  button_link: string;
}

export interface WhatWeDoSectionData {
  acf_fc_layout: 'what_we_do';
  section_id: string;
  intro_title: string;
  intro_text: string;
  services: ServiceItem[];
}

// ─── Section: Testimonials ───────────────────────────────────────────────────

export interface TestimonialItem {
  image: WPImage;
  quote: string;
  name: string;
  role: string;
}

export interface TestimonialsSectionData {
  acf_fc_layout: 'testimonials';
  section_id: string;
  title: string;
  description: string;
  bg_image: WPImage;
  bg_image_mobile: WPImage;
  items: TestimonialItem[];
}

// ─── Section: Gallery ────────────────────────────────────────────────────────

export interface GalleryItem {
  thumbnail: WPImage;
  full_image: WPImage;
  caption_line1: string;
  caption_line2: string;
}

export interface GallerySectionData {
  acf_fc_layout: 'gallery';
  section_id: string;
  title: string;
  description: string;
  items: GalleryItem[];
}

// ─── Section: Contact ────────────────────────────────────────────────────────

export interface ContactItem {
  icon_class: string; // e.g. "far fa-2x fa-comment"
  label: string;
  link: string;
}

export interface ContactSectionData {
  acf_fc_layout: 'contact';
  section_id: string;
  title: string;
  description: string;
  bg_image: WPImage;
  contact_items: ContactItem[];
  form_action_url: string; // URL to post contact form
}

// ─── Union type for all flexible content layouts ──────────────────────────────

export type PageSection =
  | HeroSectionData
  | WhatWeDoSectionData
  | TestimonialsSectionData
  | GallerySectionData
  | ContactSectionData;

// ─── Full Page ────────────────────────────────────────────────────────────────

export interface PageData {
  id: number;
  slug: string;
  title: string;
  seo_title?: string;
  seo_description?: string;
  sections: PageSection[];
}

// ─── WordPress REST API raw shapes ───────────────────────────────────────────

export interface WPRestPage {
  id: number;
  slug: string;
  title: { rendered: string };
  acf: {
    seo_title?: string;
    seo_description?: string;
    page_sections: PageSection[];
  };
}

export interface WPRestOptionsResponse {
  acf: GlobalOptions;
}
