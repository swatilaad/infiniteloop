'use client';

import { useEffect, useState } from 'react';
import Image from 'next/image';
import type { GlobalOptions } from '@/lib/types';

interface NavbarProps {
  options: GlobalOptions;
}

export default function Navbar({ options }: NavbarProps) {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 120);
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const handleNavClick = (e: React.MouseEvent<HTMLAnchorElement>, href: string) => {
    if (href.startsWith('#')) {
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
      setMenuOpen(false);
    }
  };

  return (
    <nav className={`navbar navbar-expand-md tm-navbar${scrolled ? ' scroll' : ''}`} id="tmNav">
      <div className="container">
        <div className="tm-next">
          <a
            href="#infinite"
            className="navbar-brand"
            onClick={(e) => handleNavClick(e, '#infinite')}
          >
            {options.site_logo ? (
              <Image
                src={options.site_logo.url}
                alt={options.site_logo.alt || options.site_name}
                width={120}
                height={40}
                style={{ objectFit: 'contain' }}
              />
            ) : (
              options.site_name
            )}
          </a>
        </div>

        <button
          className="navbar-toggler"
          type="button"
          aria-label="Toggle navigation"
          aria-expanded={menuOpen}
          onClick={() => setMenuOpen((prev) => !prev)}
        >
          <i className="fas fa-bars navbar-toggler-icon"></i>
        </button>

        <div className={`collapse navbar-collapse${menuOpen ? ' show' : ''}`}>
          <ul className="navbar-nav ml-auto">
            {options.nav_items.map((item, index) => (
              <li key={index} className="nav-item">
                <a
                  className="nav-link tm-nav-link"
                  href={item.link}
                  onClick={(e) => handleNavClick(e, item.link)}
                >
                  {item.label}
                </a>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </nav>
  );
}
