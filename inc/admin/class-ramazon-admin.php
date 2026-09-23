<?php
/**
 * WordPress Admin CRUD — jadval + edit sidebar (form POST, JSON emas).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ramazon_Admin
{
    public static function init(): void
    {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
        add_action('admin_post_ramazon_save_day', [__CLASS__, 'handle_save']);
        add_action('admin_post_ramazon_delete_day', [__CLASS__, 'handle_delete']);
        add_action('admin_post_ramazon_import_aladhan', [__CLASS__, 'handle_import']);
    }

    public static function menu(): void
    {
        add_menu_page(
            'Ramazon Taqvim',
            'Ramazon Taqvim',
            'manage_options',
            'ramazon-taqvim',
            [__CLASS__, 'render_page'],
            'dashicons-calendar-alt',
            26
        );
    }

    public static function assets(string $hook): void
    {
        if ($hook !== 'toplevel_page_ramazon-taqvim') {
            return;
        }
        wp_enqueue_style(
            'ramazon-admin',
            get_template_directory_uri() . '/assets/admin/admin.css',
            [],
            RAMAZON_TAQVIM_VERSION
        );
        wp_enqueue_script(
            'ramazon-admin',
            get_template_directory_uri() . '/assets/admin/admin.js',
            [],
            RAMAZON_TAQVIM_VERSION,
            true
        );
    }

    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $year = isset($_GET['year']) ? (int) $_GET['year'] : 2027;
        $city_id = isset($_GET['city_id']) ? sanitize_text_field(wp_unslash($_GET['city_id'])) : '';
        $cities = require get_template_directory() . '/includes/cities.php';
        $items = Ramazon_DB::all(array_filter([
            'year' => $year ?: null,
            'city_id' => $city_id ?: null,
        ]));

        $notice = isset($_GET['ramazon_notice']) ? sanitize_text_field(wp_unslash($_GET['ramazon_notice'])) : '';
        $edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $edit_item = $edit_id ? Ramazon_DB::find($edit_id) : null;
        $open_sidebar = isset($_GET['action']) && $_GET['action'] === 'new' || $edit_item;

        include get_template_directory() . '/inc/admin/views/list-page.php';
    }

    public static function handle_save(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Ruxsat yo‘q');
        }
        check_admin_referer('ramazon_save_day');

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $data = wp_unslash($_POST);

        if ($id > 0) {
            Ramazon_DB::update($id, $data);
            $notice = 'updated';
        } else {
            Ramazon_DB::create($data);
            $notice = 'created';
        }

        wp_safe_redirect(add_query_arg(
            [
                'page' => 'ramazon-taqvim',
                'year' => (int) ($data['year'] ?? 2027),
                'city_id' => sanitize_text_field($data['city_id'] ?? ''),
                'ramazon_notice' => $notice,
            ],
            admin_url('admin.php')
        ));
        exit;
    }

    public static function handle_delete(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Ruxsat yo‘q');
        }
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        check_admin_referer('ramazon_delete_day_' . $id);

        if ($id > 0) {
            Ramazon_DB::delete($id);
        }

        wp_safe_redirect(add_query_arg(
            [
                'page' => 'ramazon-taqvim',
                'ramazon_notice' => 'deleted',
            ],
            admin_url('admin.php')
        ));
        exit;
    }

    public static function handle_import(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Ruxsat yo‘q');
        }
        check_admin_referer('ramazon_import_aladhan');

        $year = isset($_POST['year']) ? (int) $_POST['year'] : 2027;
        $city_id = isset($_POST['city_id']) ? sanitize_text_field(wp_unslash($_POST['city_id'])) : 'tashkent';
        $result = Ramazon_DB::import_from_aladhan($year, $city_id);

        wp_safe_redirect(add_query_arg(
            [
                'page' => 'ramazon-taqvim',
                'year' => $year,
                'city_id' => $city_id,
                'ramazon_notice' => !empty($result['ok']) ? 'imported' : 'import_error',
                'count' => (int) ($result['count'] ?? 0),
            ],
            admin_url('admin.php')
        ));
        exit;
    }
}
