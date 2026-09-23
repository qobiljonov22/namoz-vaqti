<?php
/**
 * Ramazon Taqvim — MySQL orqali CRUD (JSON emas).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ramazon_DB
{
    public static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ramazon_days';
    }

    public static function install(): void
    {
        global $wpdb;
        $table = self::table();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            year smallint(4) NOT NULL DEFAULT 2027,
            city_id varchar(64) NOT NULL DEFAULT 'tashkent',
            city_name varchar(128) NOT NULL DEFAULT 'Toshkent',
            ramadan_day tinyint(2) NOT NULL DEFAULT 1,
            gregorian_date date DEFAULT NULL,
            gregorian_label varchar(64) NOT NULL DEFAULT '',
            weekday varchar(32) NOT NULL DEFAULT '',
            imsak varchar(16) NOT NULL DEFAULT '',
            fajr varchar(16) NOT NULL DEFAULT '',
            sunrise varchar(16) NOT NULL DEFAULT '',
            dhuhr varchar(16) NOT NULL DEFAULT '',
            asr varchar(16) NOT NULL DEFAULT '',
            maghrib varchar(16) NOT NULL DEFAULT '',
            isha varchar(16) NOT NULL DEFAULT '',
            is_qadr tinyint(1) NOT NULL DEFAULT 0,
            notes text NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY year_city (year, city_id),
            KEY ramadan_day (ramadan_day)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function all(array $args = []): array
    {
        global $wpdb;
        $table = self::table();
        $where = ['1=1'];
        $params = [];

        if (!empty($args['year'])) {
            $where[] = 'year = %d';
            $params[] = (int) $args['year'];
        }
        if (!empty($args['city_id'])) {
            $where[] = 'city_id = %s';
            $params[] = sanitize_text_field($args['city_id']);
        }

        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where) . " ORDER BY year DESC, city_name ASC, ramadan_day ASC";
        if ($params) {
            $sql = $wpdb->prepare($sql, $params);
        }

        $rows = $wpdb->get_results($sql, ARRAY_A);
        return is_array($rows) ? $rows : [];
    }

    public static function find(int $id): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM ' . self::table() . ' WHERE id = %d', $id),
            ARRAY_A
        );
        return $row ?: null;
    }

    public static function create(array $data): int|false
    {
        global $wpdb;
        $clean = self::sanitize($data);
        $ok = $wpdb->insert(self::table(), $clean);
        return $ok ? (int) $wpdb->insert_id : false;
    }

    public static function update(int $id, array $data): bool
    {
        global $wpdb;
        $clean = self::sanitize($data);
        $result = $wpdb->update(self::table(), $clean, ['id' => $id]);
        return $result !== false;
    }

    public static function delete(int $id): bool
    {
        global $wpdb;
        return $wpdb->delete(self::table(), ['id' => $id], ['%d']) !== false;
    }

    public static function sanitize(array $data): array
    {
        return [
            'year' => isset($data['year']) ? (int) $data['year'] : (int) date('Y'),
            'city_id' => sanitize_text_field($data['city_id'] ?? 'tashkent'),
            'city_name' => sanitize_text_field($data['city_name'] ?? 'Toshkent'),
            'ramadan_day' => isset($data['ramadan_day']) ? (int) $data['ramadan_day'] : 1,
            'gregorian_date' => self::sanitize_date($data['gregorian_date'] ?? ''),
            'gregorian_label' => sanitize_text_field($data['gregorian_label'] ?? ''),
            'weekday' => sanitize_text_field($data['weekday'] ?? ''),
            'imsak' => sanitize_text_field($data['imsak'] ?? ''),
            'fajr' => sanitize_text_field($data['fajr'] ?? ''),
            'sunrise' => sanitize_text_field($data['sunrise'] ?? ''),
            'dhuhr' => sanitize_text_field($data['dhuhr'] ?? ''),
            'asr' => sanitize_text_field($data['asr'] ?? ''),
            'maghrib' => sanitize_text_field($data['maghrib'] ?? ''),
            'isha' => sanitize_text_field($data['isha'] ?? ''),
            'is_qadr' => empty($data['is_qadr']) ? 0 : 1,
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
        ];
    }

    private static function sanitize_date($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        $ts = strtotime($value);
        return $ts ? gmdate('Y-m-d', $ts) : null;
    }

    public static function import_from_aladhan(int $year, string $city_id): array
    {
        $cities = require get_template_directory() . '/includes/cities.php';
        $city = null;
        foreach ($cities as $item) {
            if ($item['id'] === $city_id) {
                $city = $item;
                break;
            }
        }
        if (!$city) {
            return ['ok' => false, 'error' => 'Shahar topilmadi', 'count' => 0];
        }

        $year_url = 'https://api.aladhan.com/v1/islamicYearFromGregorianForRamadan/' . $year;
        $year_raw = wp_remote_get($year_url, ['timeout' => 20]);
        if (is_wp_error($year_raw)) {
            return ['ok' => false, 'error' => $year_raw->get_error_message(), 'count' => 0];
        }
        $year_body = json_decode(wp_remote_retrieve_body($year_raw), true);
        $hijri_year = (int) ($year_body['data'] ?? 0);
        if ($hijri_year < 1400) {
            return ['ok' => false, 'error' => 'Hijriy yil aniqlanmadi', 'count' => 0];
        }

        $cal_url = add_query_arg(
            [
                'city' => $city['city'],
                'country' => $city['country'],
                'method' => 3,
            ],
            "https://api.aladhan.com/v1/hijriCalendarByCity/{$hijri_year}/9"
        );
        $cal_raw = wp_remote_get($cal_url, ['timeout' => 30]);
        if (is_wp_error($cal_raw)) {
            return ['ok' => false, 'error' => $cal_raw->get_error_message(), 'count' => 0];
        }
        $cal_body = json_decode(wp_remote_retrieve_body($cal_raw), true);
        if (empty($cal_body['data']) || !is_array($cal_body['data'])) {
            return ['ok' => false, 'error' => 'Taqvim bo‘sh', 'count' => 0];
        }

        global $wpdb;
        $table = self::table();
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE year = %d AND city_id = %s",
                $year,
                $city_id
            )
        );

        $count = 0;
        foreach ($cal_body['data'] as $row) {
            $g = $row['date']['gregorian'];
            $h = $row['date']['hijri'];
            $t = $row['timings'];
            $day_num = (int) $h['day'];
            $holidays = $h['holidays'] ?? [];
            $is_qadr = 0;
            foreach ($holidays as $holiday) {
                if (stripos($holiday, 'Qadr') !== false) {
                    $is_qadr = 1;
                    break;
                }
            }
            if (in_array($day_num, [21, 23, 25, 27, 29], true)) {
                $is_qadr = 1;
            }

            $clean_time = static function ($v) {
                return preg_replace('/\s*\(.*\)$/', '', trim((string) $v));
            };

            $g_month = (int) $g['month']['number'];
            $g_day = (int) $g['day'];
            $iso = sprintf('%s-%02d-%02d', $g['year'], $g_month, $g_day);

            $id = self::create([
                'year' => $year,
                'city_id' => $city['id'],
                'city_name' => $city['name'],
                'ramadan_day' => $day_num,
                'gregorian_date' => $iso,
                'gregorian_label' => $row['date']['readable'] ?? $iso,
                'weekday' => $g['weekday']['en'] ?? '',
                'imsak' => $clean_time($t['Imsak'] ?? ''),
                'fajr' => $clean_time($t['Fajr'] ?? ''),
                'sunrise' => $clean_time($t['Sunrise'] ?? ''),
                'dhuhr' => $clean_time($t['Dhuhr'] ?? ''),
                'asr' => $clean_time($t['Asr'] ?? ''),
                'maghrib' => $clean_time($t['Maghrib'] ?? ''),
                'isha' => $clean_time($t['Isha'] ?? ''),
                'is_qadr' => $is_qadr,
                'notes' => implode(', ', $holidays),
            ]);
            if ($id) {
                $count++;
            }
        }

        return ['ok' => true, 'count' => $count, 'hijri_year' => $hijri_year];
    }
}
