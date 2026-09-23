<?php
/**
 * Aladhan Prayer Times Calendar proxy
 * Manba: https://aladhan.com/calendar/{city}/{country}
 * API:   https://api.aladhan.com/v1/calendarByCity/{year}/{month}
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$cities = require __DIR__ . '/../includes/cities.php';
$cityMap = [];
foreach ($cities as $item) {
    $cityMap[$item['id']] = $item;
}

$cityId = isset($_GET['city']) ? strtolower(trim((string) $_GET['city'])) : 'tashkent';
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
$method = isset($_GET['method']) ? (int) $_GET['method'] : 3;

if (!isset($cityMap[$cityId])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Noto\'g\'ri shahar'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($year < 2020 || $year > 2035 || $month < 1 || $month > 12) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Yil yoki oy noto\'g\'ri'], JSON_UNESCAPED_UNICODE);
    exit;
}

$city = $cityMap[$cityId];

$cacheDir = __DIR__ . '/../cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheKey = sprintf('namoz_%s_%d_%02d_m%d.json', $cityId, $year, $month, $method);
$cacheFile = $cacheDir . '/' . $cacheKey;

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 21600) {
    readfile($cacheFile);
    exit;
}

function namoz_aladhan_get(string $path, array $query = []): ?array
{
    $url = 'https://api.aladhan.com/v1' . $path;
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 25,
            'header' => "Accept: application/json\r\nUser-Agent: RamazonTaqvim-Namoz/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
        return null;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

$payload = namoz_aladhan_get(
    sprintf('/calendarByCity/%d/%d', $year, $month),
    [
        'city' => $city['city'],
        'country' => $city['country'],
        'method' => $method,
    ]
);

if (!$payload || ($payload['code'] ?? 0) !== 200 || empty($payload['data'])) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'error' => 'Aladhan calendar yuklanmadi',
        'endpoint' => sprintf('calendarByCity/%d/%d', $year, $month),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$weekdayUz = [
    'Monday' => 'Dushanba',
    'Tuesday' => 'Seshanba',
    'Wednesday' => 'Chorshanba',
    'Thursday' => 'Payshanba',
    'Friday' => 'Juma',
    'Saturday' => 'Shanba',
    'Sunday' => 'Yakshanba',
];

$monthUz = [
    1 => 'yanvar', 2 => 'fevral', 3 => 'mart', 4 => 'aprel',
    5 => 'may', 6 => 'iyun', 7 => 'iyul', 8 => 'avgust',
    9 => 'sentabr', 10 => 'oktabr', 11 => 'noyabr', 12 => 'dekabr',
];

$cleanTime = static function ($value) {
    return preg_replace('/\s*\(.*\)$/', '', trim((string) $value));
};

$days = [];
foreach ($payload['data'] as $row) {
    $g = $row['date']['gregorian'];
    $h = $row['date']['hijri'];
    $t = $row['timings'];
    $gMonth = (int) $g['month']['number'];
    $gDay = (int) $g['day'];
    $weekdayEn = $g['weekday']['en'] ?? '';

    $days[] = [
        'day' => $gDay,
        'gregorian' => sprintf('%02d %s %s', $gDay, $monthUz[$gMonth] ?? '', $g['year']),
        'gregorian_iso' => sprintf('%s-%02d-%02d', $g['year'], $gMonth, $gDay),
        'hijri' => sprintf('%s %s %s', $h['day'], $h['month']['en'] ?? '', $h['year']),
        'weekday' => $weekdayUz[$weekdayEn] ?? $weekdayEn,
        'fajr' => $cleanTime($t['Fajr'] ?? ''),
        'sunrise' => $cleanTime($t['Sunrise'] ?? ''),
        'dhuhr' => $cleanTime($t['Dhuhr'] ?? ''),
        'asr' => $cleanTime($t['Asr'] ?? ''),
        'maghrib' => $cleanTime($t['Maghrib'] ?? ''),
        'isha' => $cleanTime($t['Isha'] ?? ''),
    ];
}

$result = [
    'ok' => true,
    'source' => 'Aladhan.com',
    'api_base' => 'https://api.aladhan.com/v1',
    'endpoint' => sprintf('calendarByCity/%d/%d', $year, $month),
    'link' => sprintf(
        'https://aladhan.com/calendar/%s/%s',
        rawurlencode($city['city']),
        rawurlencode($city['country'])
    ),
    'city' => [
        'id' => $city['id'],
        'name' => $city['name'],
        'api_city' => $city['city'],
        'country' => $city['country'],
    ],
    'year' => $year,
    'month' => $month,
    'month_label' => $monthUz[$month] ?? (string) $month,
    'method' => [
        'id' => $method,
        'name' => $payload['data'][0]['meta']['method']['name'] ?? 'Muslim World League',
    ],
    'timezone' => $payload['data'][0]['meta']['timezone'] ?? 'Asia/Tashkent',
    'days' => $days,
];

$json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($cacheFile, $json);
echo $json;
