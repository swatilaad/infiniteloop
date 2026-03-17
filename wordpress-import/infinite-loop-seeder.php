<?php
/**
 * Plugin Name:  Infinite Loop — Data Seeder
 * Description:  Seeds all homepage sections and Global Settings with the Infinite Loop
 *               template content. Activate once → content is created → deactivate & delete.
 * Version:      1.0.0
 * Author:       Your Name
 *
 * HOW TO USE
 * ──────────
 * 1. Upload this file to /wp-content/plugins/infinite-loop-seeder/infinite-loop-seeder.php
 * 2. Go to Plugins → activate "Infinite Loop — Data Seeder"
 * 3. Visit any page (the seeder runs on the first request after activation)
 * 4. Check Pages → you should see a published "Home" page with all sections filled
 * 5. Check Global Settings options page → navigation and footer are set
 * 6. Deactivate and delete this plugin
 *
 * NOTES
 * ──────
 * • Image fields are intentionally left empty — upload your images via Media Library
 *   and assign them in the WordPress backend after seeding.
 * • The seeder is idempotent: re-activating it will NOT duplicate content.
 *   To re-seed from scratch, delete the "Home" page and remove the
 *   `il_data_seeded` option via WP-CLI: wp option delete il_data_seeded
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Run on first request after activation ────────────────────────────────────

add_action( 'acf/init', function () {
    if ( get_option( 'il_data_seeded' ) ) {
        return;
    }

    il_seed_global_options();
    il_seed_home_page();

    update_option( 'il_data_seeded', true );

    // Admin notice on next page load
    set_transient( 'il_seed_notice', true, 30 );
} );

// ─── Admin notice ──────────────────────────────────────────────────────────────

add_action( 'admin_notices', function () {
    if ( ! get_transient( 'il_seed_notice' ) ) {
        return;
    }
    delete_transient( 'il_seed_notice' );
    echo '<div class="notice notice-success is-dismissible">
        <p><strong>Infinite Loop Seeder:</strong> All homepage sections and Global Settings have been populated successfully. You can now deactivate and delete this plugin.</p>
    </div>';
} );

// ─── Seed: Global Options (options page) ──────────────────────────────────────

function il_seed_global_options() {
    update_field( 'site_name', 'Infinite Loop', 'option' );

    // site_logo and favicon are image fields — left empty (assign via Media Library)
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

function il_seed_home_page() {
    // Check if the home page already exists
    $existing = get_page_by_path( 'home', OBJECT, 'page' );

    if ( $existing ) {
        $page_id = $existing->ID;
    } else {
        $page_id = wp_insert_post( array(
            'post_title'   => 'Home',
            'post_name'    => 'home',
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '',
        ) );
    }

    if ( is_wp_error( $page_id ) ) {
        error_log( 'Infinite Loop Seeder: Failed to create Home page — ' . $page_id->get_error_message() );
        return;
    }

    // Optionally set as the static front page
    update_option( 'page_on_front', $page_id );
    update_option( 'show_on_front', 'page' );

    // ── SEO fields ──────────────────────────────────────────────────────────────
    update_field( 'seo_title',       'Infinite Loop — Bootstrap 4.0 Parallax Theme', $page_id );
    update_field( 'seo_description', 'Headless WordPress + Next.js website built on the Infinite Loop template.', $page_id );

    // ── Flexible Content: Page Sections ─────────────────────────────────────────
    // Each array entry represents one flexible content layout.
    // Image fields use WordPress attachment IDs — set to 0/empty until images are uploaded.

    $sections = array(

        // ── 1. Hero ─────────────────────────────────────────────────────────────
        array(
            'acf_fc_layout'    => 'hero',
            'hero_title'       => 'Infinite Loop',
            'hero_subtitle'    => "Bootstrap 4.0 Parallax Theme\nFree HTML Template by TOOPLATE",
            'hero_bg_image'    => '',  // ← upload image & set attachment ID
            'hero_arrow_target'=> '#whatwedo',
        ),

        // ── 2. What We Do ────────────────────────────────────────────────────────
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

        // ── 3. Testimonials ───────────────────────────────────────────────────────
        array(
            'acf_fc_layout'   => 'testimonials',
            'section_id'      => 'testimonials',
            'title'           => 'Testimonials',
            'description'     => 'Nulla dictum sem non eros euismod, eu placerat tortor lobortis. Suspendisse id velit eu libero pellentesque interdum. Etiam quis congue eros.',
            'bg_image'        => '',  // ← upload infinite-loop-02.jpg
            'bg_image_mobile' => '',  // ← upload infinite-loop-02-mobile.jpg
            'items'           => array(
                array(
                    'image' => '',  // ← testimonial-img-01.jpg
                    'quote' => 'This background image includes a semi-transparent overlay layer. This section also has a parallax image effect.',
                    'name'  => 'Catherine Win',
                    'role'  => 'Designer',
                ),
                array(
                    'image' => '',  // ← testimonial-img-02.jpg
                    'quote' => 'Testimonial section comes with carousel items. You can use Infinite Loop HTML CSS template for your websites.',
                    'name'  => 'Dual Rocker',
                    'role'  => 'CEO',
                ),
                array(
                    'image' => '',  // ← testimonial-img-03.jpg
                    'quote' => 'Nulla finibus ligula nec tortor convallis tincidunt. Interdum et malesuada fames ac ante ipsum primis in faucibus.',
                    'name'  => 'Sandar Soft',
                    'role'  => 'Marketing',
                ),
                array(
                    'image' => '',  // ← testimonial-img-04.jpg
                    'quote' => 'Curabitur rutrum pharetra lobortis. Pellentesque vehicula, velit quis eleifend fermentum, erat arcu aliquet neque.',
                    'name'  => 'Oliva Htoo',
                    'role'  => 'Designer',
                ),
                array(
                    'image' => '',  // ← testimonial-img-02.jpg
                    'quote' => 'Integer sit amet risus et erat imperdiet finibus. Nam lacus nunc, vulputate id ex eget, euismod auctor augue.',
                    'name'  => 'Jacob Joker',
                    'role'  => 'CTO',
                ),
            ),
        ),

        // ── 4. Gallery ────────────────────────────────────────────────────────────
        array(
            'acf_fc_layout' => 'gallery',
            'section_id'    => 'gallery',
            'title'         => 'Gallery',
            'description'   => 'Praesent sed pharetra lorem, blandit convallis mi. Aenean ornare elit ac metus lacinia, sed iaculis nibh semper. Pellentesque est urna, lobortis eu arcu a, aliquet tristique urna.',
            'items'         => array(
                array(
                    'thumbnail'      => '',  // ← gallery-tn-01.jpg
                    'full_image'     => '',  // ← gallery-img-01.jpg
                    'caption_line1'  => 'Physical Health',
                    'caption_line2'  => 'Exercise!',
                ),
                array(
                    'thumbnail'      => '',  // ← gallery-tn-02.jpg
                    'full_image'     => '',  // ← gallery-img-02.jpg
                    'caption_line1'  => 'Rain on Glass',
                    'caption_line2'  => 'Second Image',
                ),
                array(
                    'thumbnail'      => '',  // ← gallery-tn-03.jpg
                    'full_image'     => '',  // ← gallery-img-03.jpg
                    'caption_line1'  => 'Sea View',
                    'caption_line2'  => 'Mega City',
                ),
                array(
                    'thumbnail'      => '',  // ← gallery-tn-04.jpg
                    'full_image'     => '',  // ← gallery-img-04.jpg
                    'caption_line1'  => 'Dream Girl',
                    'caption_line2'  => 'Thoughts',
                ),
                array(
                    'thumbnail'      => '',  // ← gallery-tn-05.jpg
                    'full_image'     => '',  // ← gallery-img-05.jpg
                    'caption_line1'  => 'Workstation',
                    'caption_line2'  => 'Offices',
                ),
                array(
                    'thumbnail'      => '',  // ← gallery-tn-06.jpg
                    'full_image'     => '',  // ← gallery-img-06.jpg
                    'caption_line1'  => 'Just Above',
                    'caption_line2'  => 'The City',
                ),
                array(
                    'thumbnail'      => '',  // ← gallery-tn-01.jpg
                    'full_image'     => '',  // ← gallery-img-01.jpg
                    'caption_line1'  => 'Another',
                    'caption_line2'  => 'Exercise Time',
                ),
                array(
                    'thumbnail'      => '',  // ← gallery-tn-02.jpg
                    'full_image'     => '',  // ← gallery-img-02.jpg
                    'caption_line1'  => 'Repeated',
                    'caption_line2'  => 'Image Spot',
                ),
            ),
        ),

        // ── 5. Contact ────────────────────────────────────────────────────────────
        array(
            'acf_fc_layout'   => 'contact',
            'section_id'      => 'contact',
            'title'           => 'Contact Us',
            'description'     => 'Proin enim orci, tincidunt quis suscipit in, placerat nec est. Vestibulum posuere faucibus posuere. Quisque aliquam velit eget leo blandit egestas. Nulla id posuere felis, quis tristique nulla.',
            'bg_image'        => '',  // ← upload infinite-loop-03.jpg
            'contact_items'   => array(
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
