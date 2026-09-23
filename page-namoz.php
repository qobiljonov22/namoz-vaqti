<?php
/**
 * Template Name: Namoz vaqti
 * Description: Aladhan namoz vaqtlari kalendari (alohida sahifa).
 */

get_header();

$cities = require get_template_directory() . '/includes/cities.php';
$now_year = (int) date('Y');
$now_month = (int) date('n');
?>

<main class="relative z-10 mx-auto max-w-6xl px-4 py-10 md:py-14" data-page="namoz">
    <nav class="mb-8 flex flex-wrap items-center justify-center gap-4 text-sm text-mist">
        <a href="<?= esc_url(home_url('/')) ?>" class="hover:text-gold transition">Ramazon Taqvim</a>
        <span class="opacity-40">·</span>
        <span class="text-gold">Namoz vaqti</span>
    </nav>

    <header class="fade-in mb-10 text-center md:mb-12">
        <p class="mb-3 text-sm tracking-[0.25em] text-mist uppercase">api.aladhan.com · calendar</p>
        <h1 class="font-display text-4xl leading-tight text-sand md:text-6xl">
            Namoz <span class="text-gold">vaqti</span>
        </h1>
        <p class="mx-auto mt-4 max-w-xl text-mist">
            Oylik namoz jadvali — Aladhan Prayer Times Calendar
        </p>
    </header>

    <section class="fade-in mb-8 flex flex-col items-stretch gap-4 rounded-2xl border border-sage/40 bg-moss/50 p-4 backdrop-blur-sm md:flex-row md:items-end md:justify-between md:p-6" style="animation-delay:0.08s">
        <div class="grid flex-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="namoz-city" class="mb-2 block text-sm text-mist">Shahar</label>
                <select id="namoz-city" class="w-full appearance-none rounded-xl border border-sage/50 bg-night/80 px-4 py-3 text-sand outline-none ring-gold/40 transition focus:ring-2">
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= esc_attr($city['id']) ?>" <?= $city['id'] === 'tashkent' ? 'selected' : '' ?>>
                            <?= esc_html($city['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="namoz-year" class="mb-2 block text-sm text-mist">Yil</label>
                <select id="namoz-year" class="w-full appearance-none rounded-xl border border-sage/50 bg-night/80 px-4 py-3 text-sand outline-none ring-gold/40 transition focus:ring-2">
                    <?php for ($y = $now_year; $y <= $now_year + 2; $y++): ?>
                        <option value="<?= $y ?>" <?= $y === $now_year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="namoz-month" class="mb-2 block text-sm text-mist">Oy</label>
                <select id="namoz-month" class="w-full appearance-none rounded-xl border border-sage/50 bg-night/80 px-4 py-3 text-sand outline-none ring-gold/40 transition focus:ring-2">
                    <?php
                    $months = [
                        1 => 'Yanvar', 2 => 'Fevral', 3 => 'Mart', 4 => 'Aprel',
                        5 => 'May', 6 => 'Iyun', 7 => 'Iyul', 8 => 'Avgust',
                        9 => 'Sentabr', 10 => 'Oktabr', 11 => 'Noyabr', 12 => 'Dekabr',
                    ];
                    foreach ($months as $num => $label):
                    ?>
                        <option value="<?= $num ?>" <?= $num === $now_month ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div id="namoz-meta" class="text-sm text-mist md:text-right">Yuklanmoqda…</div>
    </section>

    <section id="namoz-today" class="fade-in mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" style="animation-delay:0.12s"></section>

    <section class="fade-in overflow-hidden rounded-2xl border border-sage/40 bg-moss/40 backdrop-blur-sm" style="animation-delay:0.16s">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="bg-night/60 text-mist">
                    <tr>
                        <th class="px-3 py-3 font-medium">Sana</th>
                        <th class="px-3 py-3 font-medium">Hafta</th>
                        <th class="px-3 py-3 font-medium text-gold">Bomdod</th>
                        <th class="px-3 py-3 font-medium">Quyosh</th>
                        <th class="px-3 py-3 font-medium">Peshin</th>
                        <th class="px-3 py-3 font-medium">Asr</th>
                        <th class="px-3 py-3 font-medium text-gold">Shom</th>
                        <th class="px-3 py-3 font-medium">Xufton</th>
                    </tr>
                </thead>
                <tbody id="namoz-body" class="divide-y divide-sage/25">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-mist">Namoz vaqtlari yuklanmoqda…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <p class="mt-6 text-center text-xs text-mist/70">
        Manba:
        <a id="namoz-source" class="underline decoration-gold/50 hover:text-gold" href="https://aladhan.com/calendar/Tashkent/Uzbekistan" target="_blank" rel="noopener">aladhan.com/calendar/Tashkent/Uzbekistan</a>
        · Muslim World League
    </p>
</main>

<?php
get_footer();
