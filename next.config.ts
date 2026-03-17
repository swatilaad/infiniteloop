import type { NextConfig } from 'next';

const WP_HOSTNAME = process.env.NEXT_PUBLIC_WP_BASE_URL
  ? new URL(process.env.NEXT_PUBLIC_WP_BASE_URL).hostname
  : '';

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      // WordPress / Pantheon media uploads
      ...(WP_HOSTNAME
        ? [{ protocol: 'https' as const, hostname: WP_HOSTNAME }]
        : []),
    ],
  },
};

export default nextConfig;
