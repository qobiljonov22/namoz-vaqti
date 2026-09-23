<?php
/**
 * Ramazon Taqvim theme.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RAMAZON_TAQVIM_VERSION', '1.2.0');

require_once get_template_directory() . '/inc/class-ramazon-db.php';
require_once get_template_directory() . '/inc/admin/class-ramazon-admin.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
});

add_action('after_switch_theme', ['Ramazon_DB', 'install']);
add_action('init', function () {
    Ramazon_DB::install();
});

add_action('after_setup_theme', function () {
    // «Namoz vaqti» sahifasini bir marta yaratish
    if (get_option('ramazon_namoz_page_id')) {
        return;
    }
    $existing = get_page_by_path('namoz');
    if ($existing) {
        update_option('ramazon_namoz_page_id', (int) $existing->ID);
        update_post_meta($existing->ID, '_wp_page_template', 'page-namoz.php');
        return;
    }
    $id = wp_insert_post([
        'post_title'   => 'Namoz vaqti',
        'post_name'    => 'namoz',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => '',
    ]);
    if (!is_wp_error($id) && $id) {
        update_post_meta($id, '_wp_page_template', 'page-namoz.php');
        update_option('ramazon_namoz_page_id', (int) $id);
    }
});

if (is_admin()) {
    Ramazon_Admin::init();
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'ramazon-fonts',
        'https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Manrope:wght@400;500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', [], null, false);

    $localize = [
        'ajaxUrl'  => admin_url('admin-ajax.php'),
        'restDays' => home_url('/?ramazon_days=1'),
        'themeUri' => get_template_directory_uri(),
        'namozUrl' => home_url('/namoz/'),
        'homeUrl'  => home_url('/'),
    ];

    if (is_page_template('page-namoz.php') || is_page('namoz')) {
        wp_enqueue_script(
            'ramazon-namoz',
            get_template_directory_uri() . '/assets/js/namoz.js',
            [],
            RAMAZON_TAQVIM_VERSION,
            true
        );
        wp_localize_script('ramazon-namoz', 'RamazonData', $localize);
    } else {
        wp_enqueue_script(
            'ramazon-taqvim-main',
            get_template_directory_uri() . '/assets/js/main.js',
            [],
            RAMAZON_TAQVIM_VERSION,
            true
        );
        wp_localize_script('ramazon-taqvim-main', 'RamazonData', $localize);
    }
});

/**
 * Frontend: DB dan kunlar (WP orqali).
 */
add_action('template_redirect', function () {
    if (!isset($_GET['ramazon_days'])) {
        return;
    }

    $year = isset($_GET['year']) ? (int) $_GET['year'] : 2027;
    $city_id = isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : 'tashkent';
    $days = Ramazon_DB::all([
        'year' => $year,
        'city_id' => $city_id,
    ]);

    $mapped = array_map(static function ($row) {
        return [
            'ramadan_day' => (int) $row['ramadan_day'],
            'gregorian' => $row['gregorian_label'] ?: $row['gregorian_date'],
            'gregorian_iso' => $row['gregorian_date'],
            'weekday' => $row['weekday'],
            'imsak' => $row['imsak'],
            'fajr' => $row['fajr'],
            'maghrib' => $row['maghrib'],
            'isha' => $row['isha'],
            'is_first' => (int) $row['ramadan_day'] === 1,
            'is_qadr' => (int) $row['is_qadr'] === 1,
        ];
    }, $days);

    wp_send_json([
        'ok' => true,
        'source' => 'WordPress DB',
        'city' => [
            'id' => $city_id,
            'name' => $days[0]['city_name'] ?? $city_id,
        ],
        'year' => $year,
        'days' => $mapped,
    ]);
});
