import { getGlobalOptions, getHomePage } from '@/lib/wordpress';
import type {
  HeroSectionData,
  WhatWeDoSectionData,
  TestimonialsSectionData,
  GallerySectionData,
  ContactSectionData,
} from '@/lib/types';

import Navbar from '@/components/Navbar';
import HeroSection from '@/components/sections/HeroSection';
import WhatWeDoSection from '@/components/sections/WhatWeDoSection';
import TestimonialsSection from '@/components/sections/TestimonialsSection';
import GallerySection from '@/components/sections/GallerySection';
import ContactSection from '@/components/sections/ContactSection';

export default async function Home() {
  let options;
  let page;

  try {
    [options, page] = await Promise.all([getGlobalOptions(), getHomePage()]);
  } catch (err) {
    const message = err instanceof Error ? err.message : String(err);
    return (
      <div style={{ fontFamily: 'monospace', padding: '40px', maxWidth: '700px', margin: '0 auto' }}>
        <h2 style={{ color: '#c0392b' }}>WordPress connection error</h2>
        <pre style={{ background: '#f8f8f8', padding: '16px', borderRadius: '6px', whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>
          {message}
        </pre>
        <p style={{ marginTop: '24px', color: '#555' }}>
          Check <strong>WORDPRESS_SETUP.md</strong> and make sure:
        </p>
        <ul style={{ color: '#555', lineHeight: '1.8' }}>
          <li><code>NEXT_PUBLIC_WP_BASE_URL</code> is set in <code>.env.local</code></li>
          <li>The <code>functions-snippet.php</code> code is added to <code>functions.php</code></li>
          <li>Both ACF field groups have <strong>"Show in REST API"</strong> enabled</li>
          <li>A page with slug <strong>"home"</strong> exists and is published</li>
        </ul>
      </div>
    );
  }

  return (
    <>
      <Navbar options={options} />

      {page.sections.map((section, index) => {
        switch (section.acf_fc_layout) {
          case 'hero':
            return <HeroSection key={index} data={section as HeroSectionData} />;
          case 'what_we_do':
            return <WhatWeDoSection key={index} data={section as WhatWeDoSectionData} />;
          case 'testimonials':
            return <TestimonialsSection key={index} data={section as TestimonialsSectionData} />;
          case 'gallery':
            return <GallerySection key={index} data={section as GallerySectionData} />;
          case 'contact':
            return (
              <ContactSection
                key={index}
                data={section as ContactSectionData}
                copyright={options.footer_copyright}
              />
            );
          default:
            return null;
        }
      })}
    </>
  );
}
