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

  fillCitySelect(citySelect, localStorage.getItem("ramazon_city") || "tashkent");
  const savedYear = localStorage.getItem("ramazon_year");
  if (savedYear && [...yearSelect.options].some((o) => o.value === savedYear)) {
    yearSelect.value = savedYear;
  }

  function renderHero(day, cityName) {
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
        const cls = [
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
          <tr class="${cls}">
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

  async function load() {
    const cityId = citySelect.value;
    const year = Number(yearSelect.value);
    calendarBody.innerHTML =
      '<tr><td colspan="7" class="px-4 py-10 text-center text-mist">Taqvim yuklanmoqda…</td></tr>';
    meta.textContent = "Yuklanmoqda…";
    yearLabel.textContent = String(year);

    try {
      const data = await Aladhan.ramadanCalendar(cityId, year);
      const map =
        window.AdminStore && typeof AdminStore.getMergedOverrides === "function"
          ? await AdminStore.getMergedOverrides()
          : {};
      const days =
        window.AdminStore && typeof AdminStore.applyOverrides === "function"
          ? AdminStore.applyOverrides(data.days, year, cityId, map)
          : data.days;
      hijriLabel.textContent = `Aladhan API · ${data.hijri_year} AH`;
      sourceLink.href = data.link;
      sourceLink.textContent = `aladhan.com/ramadan-calendar/${data.year}`;
      meta.innerHTML = `<span class="text-sand">${escapeHtml(data.city.name)}</span> · ${days.length} kun`;
      const featured =
        days.find((d) => d.gregorian_iso === todayIso()) || days[0];
      renderHero(featured, data.city.name);
      renderRows(days);
      document.title = `Ramazon Taqvim ${data.year}`;
    } catch (err) {
      meta.textContent = "Xatolik";
      calendarBody.innerHTML =
        '<tr><td colspan="7" class="px-4 py-10 text-center text-red-300">Taqvimni yuklab bo‘lmadi.</td></tr>';
      console.error(err);
    }
  }

  function persist() {
    localStorage.setItem("ramazon_city", citySelect.value);
    localStorage.setItem("ramazon_year", yearSelect.value);
    load();
  }

  citySelect.addEventListener("change", persist);
  yearSelect.addEventListener("change", persist);
  load();
})();
