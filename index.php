<?php
/**
 * Asosiy shablon.
 */

get_header();

$cities = require get_template_directory() . '/includes/cities.php';
?>

<main class="relative z-10 mx-auto max-w-6xl px-4 py-10 md:py-14">
    <nav class="mb-8 flex flex-wrap items-center justify-center gap-4 text-sm text-mist">
        <span class="text-gold">Ramazon Taqvim</span>
        <span class="opacity-40">·</span>
        <a href="<?= esc_url(home_url('/namoz/')) ?>" class="hover:text-gold transition">Namoz vaqti</a>
    </nav>

    <header class="fade-in mb-10 text-center md:mb-12">
        <p id="hijri-label" class="mb-3 text-sm tracking-[0.25em] text-mist uppercase">WordPress · Aladhan</p>
        <h1 class="font-display text-4xl leading-tight text-sand md:text-6xl">
            Ramazon Taqvim <span id="year-label" class="text-gold">2027</span>
        </h1>
        <p class="mx-auto mt-4 max-w-xl text-mist">
            Saharlik va Iftor vaqtlari — Admin paneldan boshqariladi
        </p>
    </header>

    <section class="fade-in mb-8 flex flex-col items-stretch gap-4 rounded-2xl border border-sage/40 bg-moss/50 p-4 backdrop-blur-sm md:flex-row md:items-end md:justify-between md:p-6" style="animation-delay:0.08s">
        <div class="grid flex-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="year" class="mb-2 block text-sm text-mist">Yil</label>
                <select id="year" class="w-full appearance-none rounded-xl border border-sage/50 bg-night/80 px-4 py-3 text-sand outline-none ring-gold/40 transition focus:ring-2">
                    <option value="2027" selected>2027 (1448 AH)</option>
                    <option value="2028">2028 (1449 AH)</option>
                </select>
            </div>
            <div>
                <label for="city" class="mb-2 block text-sm text-mist">Shahar</label>
                <select id="city" class="w-full appearance-none rounded-xl border border-sage/50 bg-night/80 px-4 py-3 text-sand outline-none ring-gold/40 transition focus:ring-2">
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= esc_attr($city['id']) ?>" <?= $city['id'] === 'tashkent' ? 'selected' : '' ?>>
                            <?= esc_html($city['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div id="meta" class="text-sm text-mist md:text-right">Yuklanmoqda…</div>
    </section>

    <section id="hero-day" class="fade-in mb-8 grid gap-4 md:grid-cols-3" style="animation-delay:0.12s"></section>

    <section class="fade-in overflow-hidden rounded-2xl border border-sage/40 bg-moss/40 backdrop-blur-sm" style="animation-delay:0.16s">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-night/60 text-mist">
                    <tr>
                        <th class="px-4 py-3 font-medium">Kun</th>
                        <th class="px-4 py-3 font-medium">Sana</th>
                        <th class="px-4 py-3 font-medium">Hafta</th>
                        <th class="px-4 py-3 font-medium text-gold">Saharlik</th>
                        <th class="px-4 py-3 font-medium">Bomdod</th>
                        <th class="px-4 py-3 font-medium text-gold">Iftor</th>
                        <th class="px-4 py-3 font-medium">Xufton</th>
                    </tr>
                </thead>
                <tbody id="calendar-body" class="divide-y divide-sage/25">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-mist">Taqvim yuklanmoqda…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <p class="mt-6 text-center text-xs text-mist/70">
        Boshqaruv:
        <a class="underline decoration-gold/50 hover:text-gold" href="<?= esc_url(admin_url('admin.php?page=ramazon-taqvim')) ?>">WordPress Admin</a>
        · Manba:
        <a id="source-link" class="underline decoration-gold/50 hover:text-gold" href="https://aladhan.com/ramadan-calendar/2027" target="_blank" rel="noopener">aladhan.com/ramadan-calendar/2027</a>
    </p>
</main>

<?php
get_footer();
