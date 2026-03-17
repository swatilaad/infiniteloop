'use client';

import { useEffect, useRef } from 'react';
import Image from 'next/image';
import Slider from 'react-slick';
import type { TestimonialsSectionData } from '@/lib/types';

interface TestimonialsSectionProps {
  data: TestimonialsSectionData;
}

export default function TestimonialsSection({ data }: TestimonialsSectionProps) {
  const sectionRef = useRef<HTMLElement>(null);

  // Parallax-2 effect (fixed background position)
  useEffect(() => {
    const section = sectionRef.current;
    if (!section) return;

    const multiplier = 1 - 0.8;

    const onScroll = () => {
      if (window.innerWidth <= 768) {
        section.style.backgroundPosition = 'center';
        return;
      }
      const firstTop = section.getBoundingClientRect().top + window.scrollY;
      const pos = window.scrollY;
      const yPos = Math.round(multiplier * (firstTop - pos) - 186);
      section.style.backgroundPosition = `center ${yPos}px`;
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const sliderSettings = {
    dots: true,
    prevArrow: false as unknown as React.ReactElement,
    nextArrow: false as unknown as React.ReactElement,
    infinite: false,
    slidesToShow: 3,
    slidesToScroll: 1,
    responsive: [
      { breakpoint: 992, settings: { slidesToShow: 2 } },
      { breakpoint: 768, settings: { slidesToShow: 2 } },
      { breakpoint: 480, settings: { slidesToShow: 1 } },
    ],
  };

  return (
    <section
      id={data.section_id}
      ref={sectionRef}
      className="tm-section-pad-top tm-parallax-2"
      style={{
        backgroundImage: `url(${data.bg_image.url})`,
        backgroundAttachment: 'fixed',
        backgroundSize: '100%',
        backgroundRepeat: 'no-repeat',
      }}
    >
      <div className="container tm-testimonials-content">
        <div className="row">
          <div className="col-lg-12 tm-content-box">
            <h2 className="text-white text-center mb-4 tm-section-title">{data.title}</h2>
            <p className="mx-auto tm-section-desc text-center">{data.description}</p>

            <div className="mx-auto tm-gallery-container tm-gallery-container-2">
              <Slider {...sliderSettings} className="tm-testimonials-carousel">
                {data.items.map((item, index) => (
                  <figure key={index} className="tm-testimonial-item">
                    <Image
                      src={item.image.url}
                      alt={item.image.alt}
                      width={290}
                      height={290}
                      className="img-fluid mx-auto"
                      style={{ borderRadius: '50%', marginBottom: '35px' }}
                    />
                    <blockquote>{item.quote}</blockquote>
                    <figcaption>
                      {item.name} ({item.role})
                    </figcaption>
                  </figure>
                ))}
              </Slider>
            </div>
          </div>
        </div>
      </div>
      <div className="tm-bg-overlay"></div>
    </section>
  );
}
