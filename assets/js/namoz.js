(() => {
  const root = document.querySelector('[data-page="namoz"]');
  if (!root) return;

  const citySelect = document.getElementById("namoz-city");
  const yearSelect = document.getElementById("namoz-year");
  const monthSelect = document.getElementById("namoz-month");
  const body = document.getElementById("namoz-body");
  const todayBox = document.getElementById("namoz-today");
  const meta = document.getElementById("namoz-meta");
  const source = document.getElementById("namoz-source");

  const themeUri =
    (window.RamazonData && window.RamazonData.themeUri) ||
    "/wp-content/themes/ramazon-taqvim";

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function todayIso() {
    const now = new Date();
    return [
      now.getFullYear(),
      String(now.getMonth() + 1).padStart(2, "0"),
      String(now.getDate()).padStart(2, "0"),
    ].join("-");
  }

  function pickToday(days) {
    const iso = todayIso();
    return days.find((d) => d.gregorian_iso === iso) || days[0];
  }

  function renderToday(day) {
    if (!todayBox || !day) return;
    const cards = [
      { label: "Bomdod", value: day.fajr, gold: true },
      { label: "Quyosh", value: day.sunrise, gold: false },
      { label: "Peshin", value: day.dhuhr, gold: false },
      { label: "Asr", value: day.asr, gold: false },
      { label: "Shom", value: day.maghrib, gold: true },
      { label: "Xufton", value: day.isha, gold: false },
    ];

    todayBox.innerHTML = cards
      .map(
        (c) => `
      <article class="rounded-2xl border ${c.gold ? "border-gold/35" : "border-sage/40"} bg-night/50 p-4">
        <p class="text-xs uppercase tracking-[0.18em] text-mist">${escapeHtml(c.label)}</p>
        <p class="mt-2 font-display text-3xl ${c.gold ? "text-gold" : "text-sand"}">${escapeHtml(c.value)}</p>
        <p class="mt-1 text-xs text-mist">${escapeHtml(day.gregorian)} · ${escapeHtml(day.weekday)}</p>
      </article>
    `
      )
      .join("");
  }

  function renderRows(days) {
    const iso = todayIso();
    body.innerHTML = days
      .map((day) => {
        const isToday = day.gregorian_iso === iso;
        return `
          <tr class="transition hover:bg-sage/20 ${isToday ? "row-today" : ""}">
            <td class="px-3 py-3 text-sand">${escapeHtml(day.gregorian)}${isToday ? ' <span class="text-gold text-[10px] uppercase">bugun</span>' : ""}</td>
            <td class="px-3 py-3 text-mist">${escapeHtml(day.weekday)}</td>
            <td class="px-3 py-3 font-semibold text-gold">${escapeHtml(day.fajr)}</td>
            <td class="px-3 py-3">${escapeHtml(day.sunrise)}</td>
            <td class="px-3 py-3">${escapeHtml(day.dhuhr)}</td>
            <td class="px-3 py-3">${escapeHtml(day.asr)}</td>
            <td class="px-3 py-3 font-semibold text-gold">${escapeHtml(day.maghrib)}</td>
            <td class="px-3 py-3">${escapeHtml(day.isha)}</td>
          </tr>
        `;
      })
      .join("");
  }

  async function load() {
    const city = citySelect.value;
    const year = yearSelect.value;
    const month = monthSelect.value;

    body.innerHTML = `
      <tr><td colspan="8" class="px-4 py-10 text-center text-mist">Namoz vaqtlari yuklanmoqda…</td></tr>
    `;
    meta.textContent = "Yuklanmoqda…";

    try {
      const url = `${themeUri}/api/namoz.php?city=${encodeURIComponent(city)}&year=${year}&month=${month}`;
      const res = await fetch(url);
      const data = await res.json();
      if (!res.ok || !data.ok) {
        throw new Error(data.error || "Xatolik");
      }

      meta.innerHTML = `
        <span class="text-sand">${escapeHtml(data.city.name)}</span>
        · ${escapeHtml(data.month_label)} ${escapeHtml(String(data.year))}
        · ${escapeHtml(String(data.days.length))} kun
      `;

      if (source && data.link) {
        source.href = data.link;
        source.textContent = `aladhan.com/calendar/${data.city.api_city}/${data.city.country}`;
      }

      renderToday(pickToday(data.days));
      renderRows(data.days);
    } catch (err) {
      body.innerHTML = `
        <tr><td colspan="8" class="px-4 py-10 text-center text-red-300">Namoz vaqtlarini yuklab bo‘lmadi.</td></tr>
      `;
      meta.textContent = "Xatolik";
      console.error(err);
    }
  }

  function persist() {
    localStorage.setItem("namoz_city", citySelect.value);
    localStorage.setItem("namoz_year", yearSelect.value);
    localStorage.setItem("namoz_month", monthSelect.value);
    load();
  }

  [citySelect, yearSelect, monthSelect].forEach((el) => {
    el.addEventListener("change", persist);
  });

  const savedCity = localStorage.getItem("namoz_city");
  if (savedCity && [...citySelect.options].some((o) => o.value === savedCity)) {
    citySelect.value = savedCity;
  }
  const savedYear = localStorage.getItem("namoz_year");
  if (savedYear && [...yearSelect.options].some((o) => o.value === savedYear)) {
    yearSelect.value = savedYear;
  }
  const savedMonth = localStorage.getItem("namoz_month");
  if (savedMonth && [...monthSelect.options].some((o) => o.value === savedMonth)) {
    monthSelect.value = savedMonth;
  }

  load();
})();
