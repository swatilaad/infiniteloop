'use client';

import { useEffect, useRef } from 'react';
import type { HeroSectionData } from '@/lib/types';

interface HeroSectionProps {
  data: HeroSectionData;
}

export default function HeroSection({ data }: HeroSectionProps) {
  const sectionRef = useRef<HTMLElement>(null);

  // Parallax effect on scroll
  useEffect(() => {
    const section = sectionRef.current;
    if (!section) return;

    const getOffset = () => {
      const h = window.innerHeight;
      if (h > 830) return 210;
      if (h > 680) return 300;
      if (h > 500) return 400;
      return 450;
    };

    const onScroll = () => {
      const multiplier = 1 - 0.3;
      const fromTop = window.scrollY;
      const offset = getOffset();
      const pos = multiplier * fromTop - offset;
      section.style.backgroundPosition = `center ${pos}px`;
    };

    const onResize = () => onScroll();

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
    onScroll();

    return () => {
      window.removeEventListener('scroll', onScroll);
      window.removeEventListener('resize', onResize);
    };
  }, []);

  const handleArrowClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
    e.preventDefault();
    const target = document.querySelector(data.hero_arrow_target);
    if (target) target.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <section
      id="infinite"
      ref={sectionRef}
      className="text-white tm-font-big tm-parallax"
      style={{ backgroundImage: `url(${data.hero_bg_image.url})` }}
    >
      <div className="text-center tm-hero-text-container">
        <div className="tm-hero-text-container-inner">
          <h2 className="tm-hero-title">{data.hero_title}</h2>
          <p className="tm-hero-subtitle">
            {data.hero_subtitle.split('\n').map((line, i, arr) => (
              <span key={i}>
                {line}
                {i < arr.length - 1 && <br />}
              </span>
            ))}
          </p>
        </div>
      </div>

      <div className="tm-next tm-intro-next">
        <a
          href={data.hero_arrow_target}
          className="text-center tm-down-arrow-link"
          onClick={handleArrowClick}
          aria-label="Scroll down"
        >
          <i className="fas fa-2x fa-arrow-down tm-down-arrow"></i>
        </a>
      </div>
    </section>
  );
}
