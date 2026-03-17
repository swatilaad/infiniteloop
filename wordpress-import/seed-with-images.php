<?php
/**
 * Plugin Name:  Infinite Loop — Full Seeder (with Images)
 * Description:  Seeds all homepage content AND uploads the template images from the
 *               Next.js public folder to the WordPress Media Library, then assigns
 *               them to all image fields automatically.
 *               Activate once → full content + images → deactivate & delete.
 * Version:      1.0.0
 *
 * PREREQUISITES
 * ──────────────
 * 1. Your Next.js public folder must be accessible from the PHP server, OR
 *    set NEXTJS_TEMPLATE_IMG_PATH below to the absolute filesystem path of:
 *    <nextjs-project>/public/template/2117_infinite_loop/img/
 *
 * 2. Both plugins must be active:
 *    - Advanced Custom Fields PRO
 *    - The ACF field groups must be imported (acf-export/infinite-loop-acf.json)
 *
 * HOW TO USE
 * ──────────
 * 1. Open this file and set NEXTJS_TEMPLATE_IMG_PATH (line ~40) to the absolute
 *    path of the template images on your server.
 * 2. Upload this file to /wp-content/plugins/il-full-seeder/seed-with-images.php
 * 3. Activate the plugin in WordPress.
 * 4. Visit any WP admin page — seeding runs automatically.
 * 5. Deactivate and delete this plugin when done.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// CONFIGURATION — set this to the absolute path of the template images folder
// Example (local):   /var/www/html/nextjs/public/template/2117_infinite_loop/img
// Example (Windows): C:/xampp/htdocs/nextjs/public/template/2117_infinite_loop/img
// ─────────────────────────────────────────────────────────────────────────────
define( 'IL_TEMPLATE_IMG_PATH', '' ); // ← SET THIS PATH

// ─── Entry point ──────────────────────────────────────────────────────────────

add_action( 'acf/init', function () {
    if ( get_option( 'il_full_seeded' ) ) {
        return;
    }

    if ( empty( IL_TEMPLATE_IMG_PATH ) || ! is_dir( IL_TEMPLATE_IMG_PATH ) ) {
        set_transient( 'il_seed_error', 'IL_TEMPLATE_IMG_PATH is not set or is not a valid directory. Please edit the plugin file.', 60 );
        return;
    }

    $images = il_upload_template_images();
    il_seed_global_options( $images );
    il_seed_home_page( $images );

    update_option( 'il_full_seeded', true );
    set_transient( 'il_seed_notice', true, 60 );
} );

// ─── Admin notices ────────────────────────────────────────────────────────────

add_action( 'admin_notices', function () {
    if ( $error = get_transient( 'il_seed_error' ) ) {
        delete_transient( 'il_seed_error' );
        echo '<div class="notice notice-error"><p><strong>Infinite Loop Full Seeder error:</strong> ' . esc_html( $error ) . '</p></div>';
    }
    if ( get_transient( 'il_seed_notice' ) ) {
        delete_transient( 'il_seed_notice' );
        echo '<div class="notice notice-success is-dismissible">
            <p><strong>Infinite Loop Full Seeder:</strong> All homepage sections, Global Settings, and images have been seeded successfully. You can now deactivate and delete this plugin.</p>
        </div>';
    }
} );

// ─── Upload template images to Media Library ─────────────────────────────────

function il_upload_template_images(): array {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $files = array(
        'hero_bg'          => 'infinite-loop-01.jpg',
        'testimonials_bg'  => 'infinite-loop-02.jpg',
        'testimonials_mob' => 'infinite-loop-02-mobile.jpg',
        'contact_bg'       => 'infinite-loop-03.jpg',
        'testimonial_1'    => 'testimonial-img-01.jpg',
        'testimonial_2'    => 'testimonial-img-02.jpg',
        'testimonial_3'    => 'testimonial-img-03.jpg',
        'testimonial_4'    => 'testimonial-img-04.jpg',
        'gallery_tn_1'     => 'gallery-tn-01.jpg',
        'gallery_tn_2'     => 'gallery-tn-02.jpg',
        'gallery_tn_3'     => 'gallery-tn-03.jpg',
        'gallery_tn_4'     => 'gallery-tn-04.jpg',
        'gallery_tn_5'     => 'gallery-tn-05.jpg',
        'gallery_tn_6'     => 'gallery-tn-06.jpg',
        'gallery_full_1'   => 'gallery-img-01.jpg',
        'gallery_full_2'   => 'gallery-img-02.jpg',
        'gallery_full_3'   => 'gallery-img-03.jpg',
        'gallery_full_4'   => 'gallery-img-04.jpg',
        'gallery_full_5'   => 'gallery-img-05.jpg',
        'gallery_full_6'   => 'gallery-img-06.jpg',
    );

    $ids = array();
    $img_path = rtrim( IL_TEMPLATE_IMG_PATH, '/\\' );

    foreach ( $files as $key => $filename ) {
        $file_path = $img_path . DIRECTORY_SEPARATOR . $filename;

        if ( ! file_exists( $file_path ) ) {
            error_log( "IL Seeder: Image not found — $file_path" );
            $ids[ $key ] = 0;
            continue;
        }

        // Check if already uploaded (avoid duplicates on re-run)
        $existing = get_posts( array(
            'post_type'  => 'attachment',
            'meta_key'   => '_il_source_file',
            'meta_value' => $filename,
            'fields'     => 'ids',
            'numberposts'=> 1,
        ) );

        if ( ! empty( $existing ) ) {
            $ids[ $key ] = $existing[0];
            continue;
        }

        // Copy to uploads temp directory so WP can process it
        $tmp = get_temp_dir() . $filename;
        copy( $file_path, $tmp );

        $file_array = array(
            'name'     => $filename,
            'tmp_name' => $tmp,
        );

        $attachment_id = media_handle_sideload( $file_array, 0, pathinfo( $filename, PATHINFO_FILENAME ) );

        if ( is_wp_error( $attachment_id ) ) {
            error_log( 'IL Seeder: Failed to upload ' . $filename . ' — ' . $attachment_id->get_error_message() );
            $ids[ $key ] = 0;
        } else {
            update_post_meta( $attachment_id, '_il_source_file', $filename );
            $ids[ $key ] = $attachment_id;
        }
    }

    return $ids;
}

// ─── Seed: Global Options ─────────────────────────────────────────────────────

function il_seed_global_options( array $images ): void {
    update_field( 'site_name', 'Infinite Loop', 'option' );
    update_field( 'site_logo', '', 'option' );
    update_field( 'favicon', '', 'option' );

    update_field( 'nav_items', array(
        array( 'label' => 'Home',         'link' => '#infinite' ),
        array( 'label' => 'What We Do',   'link' => '#whatwedo' ),
        array( 'label' => 'Testimonials', 'link' => '#testimonials' ),
        array( 'label' => 'Gallery',      'link' => '#gallery' ),
        array( 'label' => 'Contact',      'link' => '#contact' ),
    ), 'option' );

    update_field( 'footer_copyright', 'Copyright &copy; ' . date( 'Y' ) . ' Company Name', 'option' );
}

// ─── Seed: Home Page ──────────────────────────────────────────────────────────

function il_seed_home_page( array $images ): void {
    $existing = get_page_by_path( 'home', OBJECT, 'page' );

    if ( $existing ) {
        $page_id = $existing->ID;
    } else {
        $page_id = wp_insert_post( array(
            'post_title'  => 'Home',
            'post_name'   => 'home',
            'post_type'   => 'page',
            'post_status' => 'publish',
            'post_content'=> '',
        ) );
    }

    if ( is_wp_error( $page_id ) ) {
        error_log( 'IL Seeder: Failed to create Home page — ' . $page_id->get_error_message() );
        return;
    }

    update_option( 'page_on_front', $page_id );
    update_option( 'show_on_front', 'page' );

    update_field( 'seo_title',       'Infinite Loop — Bootstrap 4.0 Parallax Theme', $page_id );
    update_field( 'seo_description', 'Headless WordPress + Next.js website built on the Infinite Loop template.', $page_id );

    $sections = array(

        // ── Hero ─────────────────────────────────────────────────────────────────
        array(
            'acf_fc_layout'    => 'hero',
            'hero_title'       => 'Infinite Loop',
            'hero_subtitle'    => "Bootstrap 4.0 Parallax Theme\nFree HTML Template by TOOPLATE",
            'hero_bg_image'    => $images['hero_bg'] ?? 0,
            'hero_arrow_target'=> '#whatwedo',
        ),

        // ── What We Do ───────────────────────────────────────────────────────────
        array(
            'acf_fc_layout' => 'what_we_do',
            'section_id'    => 'whatwedo',
            'intro_title'   => 'What We Do',
            'intro_text'    => '<p>This is Infinite Loop, free Bootstrap 4.0 HTML template with a parallax effect. This layout is what you can modify and use for your websites. Please spread a word to your friends about our website. Thank you for supporting us.</p>',
            'services'      => array(
                array(
                    'icon_class'  => 'far fa-3x fa-chart-bar',
                    'title'       => 'Market Analysis',
                    'description' => '<p>Praesent sed pharetra lorem, blandit convallis mi. Aenean ornare elit ac metus lacinia, sed iaculis nibh semper. Pellentesque est urna.</p>',
                    'button_text' => '',
                    'button_link' => '',
                ),
                array(
                    'icon_class'  => 'far fa-3x fa-comment-alt',
                    'title'       => 'Fast Support',
                    'description' => '<p>Credit goes to <a href="https://www.pexels.com">Pexels</a> website for all images used in this template. Cras condimentum mi et sapien dignissim luctus.</p>',
                    'button_text' => '',
                    'button_link' => '',
                ),
                array(
                    'icon_class'  => 'fas fa-3x fa-fingerprint',
                    'title'       => 'Top Security',
                    'description' => '<p>You have <strong>no</strong> authority to post this template as a ZIP file on your template collection websites. You can <strong>use</strong> this template for your commercial websites.</p>',
                    'button_text' => 'Learn More',
                    'button_link' => '#testimonials',
                ),
                array(
                    'icon_class'  => 'fas fa-3x fa-users',
                    'title'       => 'Social Work',
                    'description' => '<p>You can change Font Awesome icons by either <b><em>fas or far</em></b> in the icon class fields. Browse icons at <a href="https://fontawesome.com/icons">fontawesome.com/icons</a>.</p>',
                    'button_text' => 'Details',
                    'button_link' => '#testimonials',
                ),
            ),
        ),

        // ── Testimonials ─────────────────────────────────────────────────────────
        array(
            'acf_fc_layout'   => 'testimonials',
            'section_id'      => 'testimonials',
            'title'           => 'Testimonials',
            'description'     => 'Nulla dictum sem non eros euismod, eu placerat tortor lobortis. Suspendisse id velit eu libero pellentesque interdum. Etiam quis congue eros.',
            'bg_image'        => $images['testimonials_bg']  ?? 0,
            'bg_image_mobile' => $images['testimonials_mob'] ?? 0,
            'items'           => array(
                array(
                    'image' => $images['testimonial_1'] ?? 0,
                    'quote' => 'This background image includes a semi-transparent overlay layer. This section also has a parallax image effect.',
                    'name'  => 'Catherine Win',
                    'role'  => 'Designer',
                ),
                array(
                    'image' => $images['testimonial_2'] ?? 0,
                    'quote' => 'Testimonial section comes with carousel items. You can use Infinite Loop HTML CSS template for your websites.',
                    'name'  => 'Dual Rocker',
                    'role'  => 'CEO',
                ),
                array(
                    'image' => $images['testimonial_3'] ?? 0,
                    'quote' => 'Nulla finibus ligula nec tortor convallis tincidunt. Interdum et malesuada fames ac ante ipsum primis in faucibus.',
                    'name'  => 'Sandar Soft',
                    'role'  => 'Marketing',
                ),
                array(
                    'image' => $images['testimonial_4'] ?? 0,
                    'quote' => 'Curabitur rutrum pharetra lobortis. Pellentesque vehicula, velit quis eleifend fermentum, erat arcu aliquet neque.',
                    'name'  => 'Oliva Htoo',
                    'role'  => 'Designer',
                ),
                array(
                    'image' => $images['testimonial_2'] ?? 0,
                    'quote' => 'Integer sit amet risus et erat imperdiet finibus. Nam lacus nunc, vulputate id ex eget, euismod auctor augue.',
                    'name'  => 'Jacob Joker',
                    'role'  => 'CTO',
                ),
            ),
        ),

        // ── Gallery ──────────────────────────────────────────────────────────────
        array(
            'acf_fc_layout' => 'gallery',
            'section_id'    => 'gallery',
            'title'         => 'Gallery',
            'description'   => 'Praesent sed pharetra lorem, blandit convallis mi. Aenean ornare elit ac metus lacinia, sed iaculis nibh semper. Pellentesque est urna, lobortis eu arcu a, aliquet tristique urna.',
            'items'         => array(
                array(
                    'thumbnail'      => $images['gallery_tn_1']   ?? 0,
                    'full_image'     => $images['gallery_full_1'] ?? 0,
                    'caption_line1'  => 'Physical Health',
                    'caption_line2'  => 'Exercise!',
                ),
                array(
                    'thumbnail'      => $images['gallery_tn_2']   ?? 0,
                    'full_image'     => $images['gallery_full_2'] ?? 0,
                    'caption_line1'  => 'Rain on Glass',
                    'caption_line2'  => 'Second Image',
                ),
                array(
                    'thumbnail'      => $images['gallery_tn_3']   ?? 0,
                    'full_image'     => $images['gallery_full_3'] ?? 0,
                    'caption_line1'  => 'Sea View',
                    'caption_line2'  => 'Mega City',
                ),
                array(
                    'thumbnail'      => $images['gallery_tn_4']   ?? 0,
                    'full_image'     => $images['gallery_full_4'] ?? 0,
                    'caption_line1'  => 'Dream Girl',
                    'caption_line2'  => 'Thoughts',
                ),
                array(
                    'thumbnail'      => $images['gallery_tn_5']   ?? 0,
                    'full_image'     => $images['gallery_full_5'] ?? 0,
                    'caption_line1'  => 'Workstation',
                    'caption_line2'  => 'Offices',
                ),
                array(
                    'thumbnail'      => $images['gallery_tn_6']   ?? 0,
                    'full_image'     => $images['gallery_full_6'] ?? 0,
                    'caption_line1'  => 'Just Above',
                    'caption_line2'  => 'The City',
                ),
                array(
                    'thumbnail'      => $images['gallery_tn_1']   ?? 0,
                    'full_image'     => $images['gallery_full_1'] ?? 0,
                    'caption_line1'  => 'Another',
                    'caption_line2'  => 'Exercise Time',
                ),
                array(
                    'thumbnail'      => $images['gallery_tn_2']   ?? 0,
                    'full_image'     => $images['gallery_full_2'] ?? 0,
                    'caption_line1'  => 'Repeated',
                    'caption_line2'  => 'Image Spot',
                ),
            ),
        ),

        // ── Contact ──────────────────────────────────────────────────────────────
        array(
            'acf_fc_layout' => 'contact',
            'section_id'    => 'contact',
            'title'         => 'Contact Us',
            'description'   => 'Proin enim orci, tincidunt quis suscipit in, placerat nec est. Vestibulum posuere faucibus posuere. Quisque aliquam velit eget leo blandit egestas. Nulla id posuere felis, quis tristique nulla.',
            'bg_image'      => $images['contact_bg'] ?? 0,
            'contact_items' => array(
                array(
                    'icon_class' => 'far fa-2x fa-comment',
                    'label'      => 'Chat Online',
                    'link'       => '#',
                ),
                array(
                    'icon_class' => 'far fa-2x fa-envelope',
                    'label'      => 'mail@company.com',
                    'link'       => 'mailto:mail@company.com',
                ),
                array(
                    'icon_class' => 'fas fa-2x fa-map-marker-alt',
                    'label'      => 'Our Location',
                    'link'       => 'https://www.google.com/maps',
                ),
                array(
                    'icon_class' => 'fas fa-2x fa-phone-square',
                    'label'      => '255-662-5566',
                    'link'       => 'tel:2556625566',
                ),
            ),
            'form_action_url' => '/api/contact',
        ),

    ); // end $sections

    update_field( 'page_sections', $sections, $page_id );
}
