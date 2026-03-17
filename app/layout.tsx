import type { Metadata } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'Infinite Loop',
  description: 'Headless WordPress + Next.js website powered by Infinite Loop theme',
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en">
      <head>
        {/* Template CSS assets served from /public */}
        <link
          rel="stylesheet"
          href="/template/2117_infinite_loop/fontawesome-5.5/css/all.min.css"
        />
        <link
          rel="stylesheet"
          href="/template/2117_infinite_loop/slick/slick.css"
        />
        <link
          rel="stylesheet"
          href="/template/2117_infinite_loop/slick/slick-theme.css"
        />
        <link
          rel="stylesheet"
          href="/template/2117_infinite_loop/css/bootstrap.min.css"
        />
        <link
          rel="stylesheet"
          href="/template/2117_infinite_loop/css/tooplate-infinite-loop.css"
        />
      </head>
      <body>{children}</body>
    </html>
  );
}
