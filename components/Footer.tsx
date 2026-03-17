import type { GlobalOptions } from '@/lib/types';

interface FooterProps {
  options: GlobalOptions;
}

// This is a standalone footer component for use outside the contact section
// if the design requires it in the future.
export default function Footer({ options }: FooterProps) {
  return (
    <footer className="text-center small tm-footer">
      <p
        className="mb-0"
        dangerouslySetInnerHTML={{ __html: options.footer_copyright }}
      />
    </footer>
  );
}
