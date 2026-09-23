(() => {
  const citySelect = document.getElementById("city");
  const yearSelect = document.getElementById("year");
  const calendarBody = document.getElementById("calendar-body");
  const heroDay = document.getElementById("hero-day");
  const meta = document.getElementById("meta");
  const yearLabel = document.getElementById("year-label");
  const hijriLabel = document.getElementById("hijri-label");
  const sourceLink = document.getElementById("source-link");

  if (!citySelect || !yearSelect || !calendarBody) return;

  const HIJRI = { 2026: 1447, 2027: 1448, 2028: 1449 };

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function todayIso() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, "0");
    const d = String(now.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
  }

  function pickFeatured(days) {
    const today = todayIso();
    return days.find((d) => d.gregorian_iso === today) || days[0];
  }

  function updateChrome(year, hijriYear) {
    if (yearLabel) yearLabel.textContent = String(year);
    if (hijriLabel) {
      hijriLabel.textContent = hijriYear
        ? `WordPress · ${hijriYear} AH`
        : "WordPress · Aladhan";
    }
    if (sourceLink) {
      sourceLink.href = `https://aladhan.com/ramadan-calendar/${year}`;
      sourceLink.textContent = `aladhan.com/ramadan-calendar/${year}`;
    }
    document.title = `Ramazon Taqvim ${year}`;
  }

  function renderHero(day, cityName) {
    if (!heroDay || !day) return;
    heroDay.innerHTML = `
      <article class="rounded-2xl border border-sage/40 bg-night/50 p-5">
        <p class="text-xs uppercase tracking-[0.2em] text-mist">Bugungi / birinchi kun</p>
        <h2 class="mt-2 font-display text-3xl text-gold">${escapeHtml(day.ramadan_day)}-kun</h2>
        <p class="mt-1 text-sand/90">${escapeHtml(day.gregorian)}</p>
        <p class="text-sm text-mist">${escapeHtml(day.weekday)} · ${escapeHtml(cityName)}</p>
      </article>
      <article class="rounded-2xl border border-gold/30 bg-night/50 p-5">
        <p class="text-xs uppercase tracking-[0.2em] text-mist">Saharlik</p>
        <p class="mt-2 font-display text-4xl text-sand">${escapeHtml(day.imsak)}</p>
        <p class="mt-1 text-sm text-mist">Imsak · Bomdod ${escapeHtml(day.fajr)}</p>
      </article>
      <article class="rounded-2xl border border-gold/30 bg-night/50 p-5">
        <p class="text-xs uppercase tracking-[0.2em] text-mist">Iftor</p>
        <p class="mt-2 font-display text-4xl text-gold">${escapeHtml(day.maghrib)}</p>
        <p class="mt-1 text-sm text-mist">Mag‘rib · Xufton ${escapeHtml(day.isha)}</p>
      </article>
    `;
  }

  function renderRows(days) {
    const today = todayIso();
    calendarBody.innerHTML = days
      .map((day) => {
        const classes = [
          "transition hover:bg-sage/20",
          day.is_qadr ? "row-qadr" : "",
          day.gregorian_iso === today ? "row-today" : "",
        ]
          .filter(Boolean)
          .join(" ");
        const badge = day.is_first
          ? '<span class="ml-2 rounded-md bg-gold/20 px-2 py-0.5 text-[10px] uppercase tracking-wide text-gold">1-kun</span>'
          : day.is_qadr
            ? '<span class="ml-2 rounded-md bg-gold/15 px-2 py-0.5 text-[10px] uppercase tracking-wide text-gold">Laylatul Qadr</span>'
            : "";
        return `
          <tr class="${classes}">
            <td class="px-4 py-3 font-medium text-sand">${escapeHtml(day.ramadan_day)}${badge}</td>
            <td class="px-4 py-3 text-mist">${escapeHtml(day.gregorian)}</td>
            <td class="px-4 py-3 text-mist">${escapeHtml(day.weekday)}</td>
            <td class="px-4 py-3 font-semibold text-gold">${escapeHtml(day.imsak)}</td>
            <td class="px-4 py-3">${escapeHtml(day.fajr)}</td>
            <td class="px-4 py-3 font-semibold text-gold">${escapeHtml(day.maghrib)}</td>
            <td class="px-4 py-3">${escapeHtml(day.isha)}</td>
          </tr>
        `;
      })
      .join("");
  }

  async function loadCalendar() {
    const cityId = citySelect.value;
    const year = Number(yearSelect.value);

    calendarBody.innerHTML = `
      <tr><td colspan="7" class="px-4 py-10 text-center text-mist">Taqvim yuklanmoqda…</td></tr>
    `;
    meta.textContent = "Yuklanmoqda…";
    updateChrome(year, HIJRI[year] || "");

    try {
      let data = null;
      const dbRes = await fetch(
        `/?ramazon_days=1&city=${encodeURIComponent(cityId)}&year=${year}`
      );
      if (dbRes.ok) {
        data = await dbRes.json();
      }

      if (!data || !data.ok || !data.days || !data.days.length) {
        const themeApi = document.body.dataset.themeUri
          ? `${document.body.dataset.themeUri}/api/ramadan.php`
          : "api/ramadan.php";
        const apiRes = await fetch(
          `${themeApi}?city=${encodeURIComponent(cityId)}&year=${year}`
        );
        data = await apiRes.json();
        if (!apiRes.ok || !data.ok) {
          throw new Error((data && data.error) || "Xatolik");
        }
      }

      updateChrome(data.year, data.hijri_year || HIJRI[year] || "");
      meta.innerHTML = `
        <span class="text-sand">${escapeHtml(data.city.name)}</span>
        · ${escapeHtml(String(data.days.length))} kun
        · ${escapeHtml(data.source || "DB")}
      `;
      renderHero(pickFeatured(data.days), data.city.name);
      renderRows(data.days);
    } catch (err) {
      calendarBody.innerHTML = `
        <tr>
          <td colspan="7" class="px-4 py-10 text-center text-red-300">
            Taqvim bo‘sh. Admin → Aladhan’dan import qiling.
          </td>
        </tr>
      `;
      meta.textContent = "Ma’lumot yo‘q";
      console.error(err);
    }
  }

  function persistAndLoad() {
    localStorage.setItem("ramazon_city", citySelect.value);
    localStorage.setItem("ramazon_year", yearSelect.value);
    loadCalendar();
  }

  citySelect.addEventListener("change", persistAndLoad);
  yearSelect.addEventListener("change", persistAndLoad);

  const savedCity = localStorage.getItem("ramazon_city");
  if (savedCity && [...citySelect.options].some((o) => o.value === savedCity)) {
    citySelect.value = savedCity;
  }
  const savedYear = localStorage.getItem("ramazon_year");
  if (savedYear && [...yearSelect.options].some((o) => o.value === savedYear)) {
    yearSelect.value = savedYear;
  }

  loadCalendar();
})();
