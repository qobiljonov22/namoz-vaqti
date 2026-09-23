<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap ramazon-admin-wrap">
    <h1 class="wp-heading-inline">Ramazon Taqvim</h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=ramazon-taqvim&action=new&year=' . $year)); ?>" class="page-title-action ramazon-open-sidebar" data-mode="new">
        Yangi kun
    </a>
    <hr class="wp-header-end">

    <?php if ($notice === 'created') : ?>
        <div class="notice notice-success is-dismissible"><p>Yangi kun qo‘shildi.</p></div>
    <?php elseif ($notice === 'updated') : ?>
        <div class="notice notice-success is-dismissible"><p>Kun yangilandi.</p></div>
    <?php elseif ($notice === 'deleted') : ?>
        <div class="notice notice-success is-dismissible"><p>Kun o‘chirildi.</p></div>
    <?php elseif ($notice === 'imported') : ?>
        <div class="notice notice-success is-dismissible"><p>Aladhan’dan <?php echo (int) ($_GET['count'] ?? 0); ?> kun yuklandi.</p></div>
    <?php elseif ($notice === 'import_error') : ?>
        <div class="notice notice-error is-dismissible"><p>Import xatosi. Qayta urinib ko‘ring.</p></div>
    <?php endif; ?>

    <div class="ramazon-toolbar">
        <form method="get" class="ramazon-filters">
            <input type="hidden" name="page" value="ramazon-taqvim">
            <label>
                Yil
                <select name="year">
                    <option value="2027" <?php selected($year, 2027); ?>>2027</option>
                    <option value="2028" <?php selected($year, 2028); ?>>2028</option>
                    <option value="2026" <?php selected($year, 2026); ?>>2026</option>
                </select>
            </label>
            <label>
                Shahar
                <select name="city_id">
                    <option value="">Barchasi</option>
                    <?php foreach ($cities as $city) : ?>
                        <option value="<?php echo esc_attr($city['id']); ?>" <?php selected($city_id, $city['id']); ?>>
                            <?php echo esc_html($city['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="button">Filtrlash</button>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ramazon-import-form">
            <?php wp_nonce_field('ramazon_import_aladhan'); ?>
            <input type="hidden" name="action" value="ramazon_import_aladhan">
            <input type="hidden" name="year" value="<?php echo esc_attr($year ?: 2027); ?>">
            <input type="hidden" name="city_id" value="<?php echo esc_attr($city_id ?: 'tashkent'); ?>">
            <button type="submit" class="button button-primary">Aladhan’dan import</button>
        </form>
    </div>

    <table class="wp-list-table widefat fixed striped ramazon-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Yil</th>
                <th>Shahar</th>
                <th>Kun</th>
                <th>Sana</th>
                <th>Saharlik</th>
                <th>Bomdod</th>
                <th>Iftor</th>
                <th>Xufton</th>
                <th>Amallar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$items) : ?>
                <tr>
                    <td colspan="10">Ma’lumot yo‘q. «Aladhan’dan import» bosing.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td><?php echo (int) $item['id']; ?></td>
                        <td><?php echo (int) $item['year']; ?></td>
                        <td><?php echo esc_html($item['city_name']); ?></td>
                        <td>
                            <?php echo (int) $item['ramadan_day']; ?>
                            <?php if (!empty($item['is_qadr'])) : ?>
                                <span class="ramazon-badge">Qadr</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($item['gregorian_label'] ?: $item['gregorian_date']); ?></td>
                        <td><strong><?php echo esc_html($item['imsak']); ?></strong></td>
                        <td><?php echo esc_html($item['fajr']); ?></td>
                        <td><strong><?php echo esc_html($item['maghrib']); ?></strong></td>
                        <td><?php echo esc_html($item['isha']); ?></td>
                        <td class="ramazon-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=ramazon-taqvim&edit=' . (int) $item['id'] . '&year=' . (int) $item['year'] . '&city_id=' . rawurlencode($item['city_id']))); ?>"
                               class="ramazon-edit-link"
                               data-item="<?php echo esc_attr(wp_json_encode($item)); ?>">
                                Edit
                            </a>
                            |
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ramazon_delete_day&id=' . (int) $item['id']), 'ramazon_delete_day_' . (int) $item['id'])); ?>"
                               class="ramazon-delete-link"
                               onclick="return confirm('O‘chirasizmi?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="ramazon-sidebar-overlay" class="ramazon-overlay<?php echo $open_sidebar ? ' is-open' : ''; ?>"></div>
<aside id="ramazon-sidebar" class="ramazon-sidebar<?php echo $open_sidebar ? ' is-open' : ''; ?>" aria-hidden="<?php echo $open_sidebar ? 'false' : 'true'; ?>">
    <div class="ramazon-sidebar-header">
        <h2 id="ramazon-sidebar-title"><?php echo $edit_item ? 'Tahrirlash' : 'Yangi kun'; ?></h2>
        <button type="button" class="ramazon-sidebar-close" aria-label="Yopish">&times;</button>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ramazon-sidebar-form">
        <?php wp_nonce_field('ramazon_save_day'); ?>
        <input type="hidden" name="action" value="ramazon_save_day">
        <input type="hidden" name="id" id="field-id" value="<?php echo (int) ($edit_item['id'] ?? 0); ?>">

        <p>
            <label for="field-year">Yil</label>
            <select name="year" id="field-year" required>
                <option value="2026" <?php selected((int) ($edit_item['year'] ?? $year), 2026); ?>>2026</option>
                <option value="2027" <?php selected((int) ($edit_item['year'] ?? $year), 2027); ?>>2027</option>
                <option value="2028" <?php selected((int) ($edit_item['year'] ?? $year), 2028); ?>>2028</option>
            </select>
        </p>

        <p>
            <label for="field-city_id">Shahar</label>
            <select name="city_id" id="field-city_id" required>
                <?php foreach ($cities as $city) : ?>
                    <option value="<?php echo esc_attr($city['id']); ?>"
                        data-name="<?php echo esc_attr($city['name']); ?>"
                        <?php selected(($edit_item['city_id'] ?? $city_id ?: 'tashkent'), $city['id']); ?>>
                        <?php echo esc_html($city['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="city_name" id="field-city_name" value="<?php echo esc_attr($edit_item['city_name'] ?? 'Toshkent'); ?>">
        </p>

        <p>
            <label for="field-ramadan_day">Ramazon kuni</label>
            <input type="number" min="1" max="30" name="ramadan_day" id="field-ramadan_day" value="<?php echo (int) ($edit_item['ramadan_day'] ?? 1); ?>" required>
        </p>

        <p>
            <label for="field-gregorian_date">Sana (YYYY-MM-DD)</label>
            <input type="date" name="gregorian_date" id="field-gregorian_date" value="<?php echo esc_attr($edit_item['gregorian_date'] ?? ''); ?>">
        </p>

        <p>
            <label for="field-gregorian_label">Sana matni</label>
            <input type="text" name="gregorian_label" id="field-gregorian_label" value="<?php echo esc_attr($edit_item['gregorian_label'] ?? ''); ?>">
        </p>

        <p>
            <label for="field-weekday">Hafta kuni</label>
            <input type="text" name="weekday" id="field-weekday" value="<?php echo esc_attr($edit_item['weekday'] ?? ''); ?>">
        </p>

        <div class="ramazon-time-grid">
            <p>
                <label for="field-imsak">Saharlik (Imsak)</label>
                <input type="text" name="imsak" id="field-imsak" value="<?php echo esc_attr($edit_item['imsak'] ?? ''); ?>" required>
            </p>
            <p>
                <label for="field-fajr">Bomdod</label>
                <input type="text" name="fajr" id="field-fajr" value="<?php echo esc_attr($edit_item['fajr'] ?? ''); ?>">
            </p>
            <p>
                <label for="field-maghrib">Iftor</label>
                <input type="text" name="maghrib" id="field-maghrib" value="<?php echo esc_attr($edit_item['maghrib'] ?? ''); ?>" required>
            </p>
            <p>
                <label for="field-isha">Xufton</label>
                <input type="text" name="isha" id="field-isha" value="<?php echo esc_attr($edit_item['isha'] ?? ''); ?>">
            </p>
        </div>

        <p>
            <label>
                <input type="checkbox" name="is_qadr" id="field-is_qadr" value="1" <?php checked(!empty($edit_item['is_qadr'])); ?>>
                Laylatul Qadr
            </label>
        </p>

        <p>
            <label for="field-notes">Izoh</label>
            <textarea name="notes" id="field-notes" rows="3"><?php echo esc_textarea($edit_item['notes'] ?? ''); ?></textarea>
        </p>

        <div class="ramazon-sidebar-actions">
            <button type="submit" class="button button-primary">Saqlash</button>
            <button type="button" class="button ramazon-sidebar-close">Bekor</button>
        </div>
    </form>
</aside>
