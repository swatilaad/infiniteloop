import type { WhatWeDoSectionData } from '@/lib/types';

interface WhatWeDoSectionProps {
  data: WhatWeDoSectionData;
}

export default function WhatWeDoSection({ data }: WhatWeDoSectionProps) {
  // Split services into pairs of 2 for the two-column layout
  const rows: (typeof data.services)[] = [];
  for (let i = 0; i < data.services.length; i += 2) {
    rows.push(data.services.slice(i, i + 2));
  }

  return (
    <section id={data.section_id} className="tm-section-pad-top">
      <div className="container">
        {/* Intro row */}
        <div className="row tm-content-box">
          <div className="col-lg-12 col-xl-12">
            <div className="tm-intro-text-container">
              <h2 className="tm-text-primary mb-4 tm-section-title">{data.intro_title}</h2>
              <p
                className="mb-4 tm-intro-text"
                dangerouslySetInnerHTML={{ __html: data.intro_text }}
              />
            </div>
          </div>
        </div>

        {/* Service rows */}
        {rows.map((row, rowIndex) => (
          <div key={rowIndex} className="row tm-content-box">
            {row.map((service, colIndex) => (
              <>
                <div key={`icon-${rowIndex}-${colIndex}`} className="col-lg-1">
                  <i className={`${service.icon_class} text-center tm-icon`}></i>
                </div>
                <div key={`content-${rowIndex}-${colIndex}`} className="col-lg-5">
                  <div className="tm-intro-text-container">
                    <h2 className="tm-text-primary mb-4">{service.title}</h2>
                    <p
                      className="mb-4 tm-intro-text"
                      dangerouslySetInnerHTML={{ __html: service.description }}
                    />
                    {service.button_text && service.button_link && (
                      <div className="tm-continue">
                        <a href={service.button_link} className="tm-intro-text tm-btn-primary">
                          {service.button_text}
                        </a>
                      </div>
                    )}
                  </div>
                </div>
              </>
            ))}
          </div>
        ))}
      </div>
    </section>
  );
}
