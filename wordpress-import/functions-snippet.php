<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  Infinite Loop — WordPress functions.php snippets
 *  Add ALL of the code below to your theme's functions.php
 *  (or to a custom plugin file).
 * ════════════════════════════════════════════════════════════════════
 */

// ─── 1. Register the ACF Options Page ────────────────────────────────────────

add_action( 'acf/init', function () {
    if ( function_exists( 'acf_add_options_page' ) ) {
        acf_add_options_page( array(
            'page_title' => 'Global Settings',
            'menu_title' => 'Global Settings',
            'menu_slug'  => 'global-settings',
            'capability' => 'edit_posts',
            'redirect'   => false,
        ) );
    }
} );

// ─── 2. Enable ACF fields in WP REST API ─────────────────────────────────────
//  This makes ACF fields appear inside page.acf in the REST response.
//  Required so the Next.js frontend can read page content.

add_filter( 'acf/settings/rest_api_format', function () {
    return 'standard';
} );

// ─── 3. Custom REST endpoint: /wp-json/il/v1/options ─────────────────────────
//  Exposes the Global Settings options page fields as JSON.
//  Used by Next.js to load site name, navigation, and footer.
//  This replaces the need for the "ACF to REST API" plugin.

add_action( 'rest_api_init', function () {
    register_rest_route( 'il/v1', '/options', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'il_rest_get_options',
        'permission_callback' => '__return_true', // public read
    ) );
} );

function il_rest_get_options(): WP_REST_Response {
    $logo    = get_field( 'site_logo', 'option' );
    $favicon = get_field( 'favicon', 'option' );

    $nav_raw  = get_field( 'nav_items', 'option' );
    $nav_items = array();
    if ( is_array( $nav_raw ) ) {
        foreach ( $nav_raw as $item ) {
            $nav_items[] = array(
                'label' => sanitize_text_field( $item['label'] ?? '' ),
                'link'  => esc_url_raw( $item['link'] ?? '' ),
            );
        }
    }

    $data = array(
        'site_name'         => get_field( 'site_name', 'option' ) ?: get_bloginfo( 'name' ),
        'site_logo'         => $logo ? array(
            'url'    => $logo['url'],
            'alt'    => $logo['alt'] ?: get_bloginfo( 'name' ),
            'width'  => $logo['width']  ?? null,
            'height' => $logo['height'] ?? null,
        ) : null,
        'favicon'           => $favicon ? array(
            'url' => $favicon['url'],
            'alt' => $favicon['alt'] ?? '',
        ) : null,
        'nav_items'         => $nav_items,
        'footer_copyright'  => get_field( 'footer_copyright', 'option' ) ?: 'Copyright &copy; ' . date( 'Y' ),
    );

    return new WP_REST_Response( $data, 200 );
}

// ─── 4. Allow CORS from your Next.js frontend ────────────────────────────────
//  Replace the origin below with your production Next.js URL when deploying.

add_action( 'rest_api_init', function () {
    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

    add_filter( 'rest_pre_serve_request', function ( $value ) {
        $allowed_origins = array(
            'http://localhost:3000',
            'http://localhost:3001',
            // Add your production domain here:
            // 'https://your-nextjs-site.com',
        );

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ( in_array( $origin, $allowed_origins, true ) ) {
            header( 'Access-Control-Allow-Origin: ' . $origin );
        } else {
            // Allow all origins during development — tighten for production
            header( 'Access-Control-Allow-Origin: *' );
        }

        header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
        header( 'Access-Control-Allow-Credentials: true' );
        header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce' );

        return $value;
    } );
}, 15 );
