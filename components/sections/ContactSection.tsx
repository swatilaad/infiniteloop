'use client';

import { useEffect, useRef, useState } from 'react';
import type { ContactSectionData } from '@/lib/types';

interface ContactSectionProps {
  data: ContactSectionData;
  copyright: string;
}

export default function ContactSection({ data, copyright }: ContactSectionProps) {
  const sectionRef = useRef<HTMLElement>(null);
  const [formState, setFormState] = useState({ name: '', email: '', message: '' });
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');

  // Parallax-2 effect
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

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    setFormState((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      const res = await fetch(data.form_action_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formState),
      });

      if (!res.ok) throw new Error('Failed to send message');
      setSubmitted(true);
      setFormState({ name: '', email: '', message: '' });
    } catch {
      setError('Something went wrong. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <section
      id={data.section_id}
      ref={sectionRef}
      className="tm-section-pad-top tm-parallax-2"
      style={{
        backgroundImage: `url(${data.bg_image.url})`,
        backgroundAttachment: 'fixed',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        backgroundColor: '#001828',
        minHeight: '980px',
        position: 'relative',
        paddingBottom: '50px',
        paddingTop: '100px',
      }}
    >
      <div className="container tm-container-contact">
        <div className="row">
          <div className="text-center col-12">
            <h2 className="tm-section-title mb-4">{data.title}</h2>
            <p className="mb-5">{data.description}</p>
            <br />
          </div>

          {/* Contact Form */}
          <div className="col-sm-12 col-md-6">
            {submitted ? (
              <p className="text-white" style={{ fontSize: '1.1rem' }}>
                ✓ Thank you! Your message has been sent.
              </p>
            ) : (
              <form onSubmit={handleSubmit}>
                <input
                  id="name"
                  name="name"
                  type="text"
                  placeholder="Your Name"
                  className="tm-input"
                  required
                  value={formState.name}
                  onChange={handleChange}
                  disabled={submitting}
                />
                <input
                  id="email"
                  name="email"
                  type="email"
                  placeholder="Your Email"
                  className="tm-input"
                  required
                  value={formState.email}
                  onChange={handleChange}
                  disabled={submitting}
                />
                <textarea
                  id="message"
                  name="message"
                  rows={8}
                  placeholder="Message"
                  className="tm-input"
                  required
                  value={formState.message}
                  onChange={handleChange}
                  disabled={submitting}
                />
                {error && <p style={{ color: '#f88', marginBottom: '10px' }}>{error}</p>}
                <button type="submit" className="btn tm-btn-submit" disabled={submitting}>
                  {submitting ? 'Sending...' : 'Submit'}
                </button>
              </form>
            )}
          </div>

          {/* Contact Info */}
          <div className="col-sm-12 col-md-6">
            {data.contact_items.map((item, index) => (
              <div key={index} className="contact-item">
                <a rel="nofollow" href={item.link} className="item-link">
                  <i className={`${item.icon_class} mr-4`}></i>
                  <span className="mb-0">{item.label}</span>
                </a>
              </div>
            ))}
            <div className="contact-item">&nbsp;</div>
          </div>
        </div>
      </div>

      <footer className="text-center small tm-footer">
        <p className="mb-0" dangerouslySetInnerHTML={{ __html: copyright }} />
      </footer>
    </section>
  );
}
