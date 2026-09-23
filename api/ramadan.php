<?php

/**
 * Aladhan.com Ramadan Calendar API proxy
 * Manba: https://aladhan.com/ramadan-calendar/{year}
 * API:   https://api.aladhan.com/v1
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$cities = require __DIR__ . '/../includes/cities.php';
$cityMap = [];
foreach ($cities as $item) {
    $cityMap[$item['id']] = $item;
}

$cityId = isset($_GET['city']) ? strtolower(trim((string) $_GET['city'])) : 'tashkent';
$year = isset($_GET['year']) ? (int) $_GET['year'] : 2027;
$method = isset($_GET['method']) ? (int) $_GET['method'] : 3;

$allowedYears = [2026, 2027, 2028];

if (!isset($cityMap[$cityId])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Noto\'g\'ri shahar'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($year, $allowedYears, true)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Yil mavjud emas. Mavjud: ' . implode(', ', $allowedYears),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$city = $cityMap[$cityId];
$hijriMonth = 9; // Ramazon

$cacheDir = __DIR__ . '/../cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheKey = sprintf('aladhan_%s_%d_m%d.json', $cityId, $year, $method);
$cacheFile = $cacheDir . '/' . $cacheKey;

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
    readfile($cacheFile);
    exit;
}

/**
 * Aladhan HTTP GET
 */
function aladhan_get(string $path, array $query = []): ?array
{
    $url = 'https://api.aladhan.com/v1' . $path;
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'header' => "Accept: application/json\r\nUser-Agent: RamazonTaqvim/1.0 (Aladhan)\r\n",
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

// 1) Aladhan: Gregorian yil → Ramazon Hijriy yili
//    GET /v1/islamicYearFromGregorianForRamadan/{year}
//    (aladhan.com/ramadan-calendar/{year} shu endpointdan foydalanadi)
$yearPayload = aladhan_get('/islamicYearFromGregorianForRamadan/' . $year);
if (!$yearPayload || ($yearPayload['code'] ?? 0) !== 200 || empty($yearPayload['data'])) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'error' => 'Aladhan: Hijriy yilni aniqlab bo\'lmadi',
        'endpoint' => 'islamicYearFromGregorianForRamadan/' . $year,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$hijriYear = (int) $yearPayload['data'];

// 2) Aladhan: Ramazon oyi taqvimi (shahar bo‘yicha)
//    GET /v1/hijriCalendarByCity/{hijriYear}/9?city=&country=&method=
$calendarPayload = aladhan_get(
    sprintf('/hijriCalendarByCity/%d/%d', $hijriYear, $hijriMonth),
    [
        'city' => $city['city'],
        'country' => $city['country'],
        'method' => $method,
    ]
);

if (
    !$calendarPayload
    || ($calendarPayload['code'] ?? 0) !== 200
    || empty($calendarPayload['data'])
) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'error' => 'Aladhan: Ramazon taqvimini yuklab bo\'lmadi',
        'endpoint' => sprintf('hijriCalendarByCity/%d/%d', $hijriYear, $hijriMonth),
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
foreach ($calendarPayload['data'] as $row) {
    $g = $row['date']['gregorian'];
    $h = $row['date']['hijri'];
    $t = $row['timings'];
    $holidays = $h['holidays'] ?? [];
    $dayNum = (int) $h['day'];

    $isQadr = false;
    foreach ($holidays as $holiday) {
        if (stripos($holiday, 'Qadr') !== false) {
            $isQadr = true;
            break;
        }
    }
    if (in_array($dayNum, [21, 23, 25, 27, 29], true)) {
        $isQadr = true;
    }

    $gMonth = (int) $g['month']['number'];
    $gDay = (int) $g['day'];
    $weekdayEn = $g['weekday']['en'] ?? '';

    $days[] = [
        'ramadan_day' => $dayNum,
        'gregorian' => sprintf('%02d %s %s', $gDay, $monthUz[$gMonth] ?? $g['month']['en'], $g['year']),
        'gregorian_iso' => sprintf('%s-%02d-%02d', $g['year'], $gMonth, $gDay),
        'hijri' => sprintf('%d-Ramzon %s', $dayNum, $h['year']),
        'weekday' => $weekdayUz[$weekdayEn] ?? $weekdayEn,
        'imsak' => $cleanTime($t['Imsak'] ?? ''),
        'fajr' => $cleanTime($t['Fajr'] ?? ''),
        'sunrise' => $cleanTime($t['Sunrise'] ?? ''),
        'dhuhr' => $cleanTime($t['Dhuhr'] ?? ''),
        'asr' => $cleanTime($t['Asr'] ?? ''),
        'maghrib' => $cleanTime($t['Maghrib'] ?? ''),
        'isha' => $cleanTime($t['Isha'] ?? ''),
        'holidays' => $holidays,
        'is_first' => $dayNum === 1,
        'is_qadr' => $isQadr,
    ];
}

$aladhanCalendarUrl = sprintf('https://aladhan.com/ramadan-calendar/%d', $year);
$aladhanCityUrl = sprintf(
    'https://aladhan.com/ramadan-prayer-times/%d/%s/%s',
    $year,
    rawurlencode($city['city']),
    rawurlencode($city['country'])
);

$result = [
    'ok' => true,
    'source' => 'Aladhan.com',
    'api_base' => 'https://api.aladhan.com/v1',
    'endpoints' => [
        'ramadan_year' => 'islamicYearFromGregorianForRamadan/' . $year,
        'calendar' => sprintf('hijriCalendarByCity/%d/%d', $hijriYear, $hijriMonth),
    ],
    'links' => [
        'ramadan_calendar' => $aladhanCalendarUrl,
        'ramadan_prayer_times' => $aladhanCityUrl,
        'home' => 'https://aladhan.com/',
    ],
    'city' => [
        'id' => $city['id'],
        'name' => $city['name'],
        'api_city' => $city['city'],
        'country' => $city['country'],
    ],
    'year' => $year,
    'hijri_year' => $hijriYear,
    'hijri_month' => $hijriMonth,
    'method' => [
        'id' => $method,
        'name' => $calendarPayload['data'][0]['meta']['method']['name'] ?? 'Muslim World League',
    ],
    'timezone' => $calendarPayload['data'][0]['meta']['timezone'] ?? 'Asia/Tashkent',
    'days' => $days,
];

$json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($cacheFile, $json);
echo $json;
