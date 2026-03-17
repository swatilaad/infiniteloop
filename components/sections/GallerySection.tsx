'use client';

import { useState } from 'react';
import Image from 'next/image';
import Slider from 'react-slick';
import Lightbox from 'yet-another-react-lightbox';
import 'yet-another-react-lightbox/styles.css';
import type { GallerySectionData } from '@/lib/types';

interface GallerySectionProps {
  data: GallerySectionData;
}

export default function GallerySection({ data }: GallerySectionProps) {
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);

  const slides = data.items.map((item) => ({
    src: item.full_image.url,
    alt: item.full_image.alt,
  }));

  const openLightbox = (index: number) => {
    setLightboxIndex(index);
    setLightboxOpen(true);
  };

  const sliderSettings = {
    dots: true,
    infinite: false,
    slidesToShow: 5,
    slidesToScroll: 2,
    responsive: [
      { breakpoint: 1199, settings: { slidesToShow: 4, slidesToScroll: 2 } },
      { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 2 } },
      { breakpoint: 767, settings: { slidesToShow: 2, slidesToScroll: 1 } },
      { breakpoint: 480, settings: { slidesToShow: 1, slidesToScroll: 1 } },
    ],
  };

  return (
    <section id={data.section_id} className="tm-section-pad-top">
      <div className="container tm-container-gallery">
        <div className="row">
          <div className="text-center col-12">
            <h2 className="tm-text-primary tm-section-title mb-4">{data.title}</h2>
            <p className="mx-auto tm-section-desc">{data.description}</p>
          </div>
        </div>

        <div className="row">
          <div className="col-12">
            <div className="mx-auto tm-gallery-container">
              <Slider {...sliderSettings} className="grid tm-gallery">
                {data.items.map((item, index) => (
                  <div key={index}>
                    <button
                      className="tm-gallery-item-btn"
                      onClick={() => openLightbox(index)}
                      style={{ background: 'none', border: 'none', padding: 0, cursor: 'pointer' }}
                      aria-label={`Open ${item.caption_line1} ${item.caption_line2}`}
                    >
                      <figure className="effect-honey tm-gallery-item">
                        <Image
                          src={item.thumbnail.url}
                          alt={item.thumbnail.alt}
                          width={220}
                          height={220}
                          className="img-fluid"
                          style={{ objectFit: 'cover' }}
                        />
                        <figcaption>
                          <h2>
                            <i>
                              {item.caption_line1} <span>{item.caption_line2}</span>
                            </i>
                          </h2>
                        </figcaption>
                      </figure>
                    </button>
                  </div>
                ))}
              </Slider>
            </div>
          </div>
        </div>
      </div>

      <Lightbox
        open={lightboxOpen}
        close={() => setLightboxOpen(false)}
        slides={slides}
        index={lightboxIndex}
      />
    </section>
  );
}
