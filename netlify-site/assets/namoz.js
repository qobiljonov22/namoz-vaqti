(() => {
  const citySelect = document.getElementById("namoz-city");
  const yearSelect = document.getElementById("namoz-year");
  const monthSelect = document.getElementById("namoz-month");
  const body = document.getElementById("namoz-body");
  const todayBox = document.getElementById("namoz-today");
  const meta = document.getElementById("namoz-meta");
  const source = document.getElementById("namoz-source");
  if (!citySelect || !yearSelect || !monthSelect || !body) return;

  const now = new Date();
  const nowYear = now.getFullYear();
  const nowMonth = now.getMonth() + 1;

  fillCitySelect(citySelect, localStorage.getItem("namoz_city") || "tashkent");

  yearSelect.innerHTML = [nowYear, nowYear + 1, nowYear + 2]
    .map((y) => `<option value="${y}" ${y === nowYear ? "selected" : ""}>${y}</option>`)
    .join("");

  monthSelect.innerHTML = Object.entries(MONTH_UZ)
    .map(
      ([num, label]) =>
        `<option value="${num}" ${Number(num) === nowMonth ? "selected" : ""}>${label.charAt(0).toUpperCase() + label.slice(1)}</option>`
    )
    .join("");

  const savedYear = localStorage.getItem("namoz_year");
  const savedMonth = localStorage.getItem("namoz_month");
  if (savedYear && [...yearSelect.options].some((o) => o.value === savedYear)) {
    yearSelect.value = savedYear;
  }
  if (savedMonth && [...monthSelect.options].some((o) => o.value === savedMonth)) {
    monthSelect.value = savedMonth;
  }

  function renderToday(day) {
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
      <article class="rounded-2xl border ${c.gold ? "border-gold/30" : "border-sage/40"} bg-night/50 p-4">
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
            <td class="px-3 py-3 text-sand">${escapeHtml(day.gregorian)}${isToday ? ' <span class="ml-1 rounded bg-gold/20 px-1.5 py-0.5 text-[10px] uppercase text-gold">bugun</span>' : ""}</td>
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
    body.innerHTML =
      '<tr><td colspan="8" class="px-4 py-10 text-center text-mist">Namoz vaqtlari yuklanmoqda…</td></tr>';
    meta.textContent = "Yuklanmoqda…";

    try {
      const data = await Aladhan.prayerCalendar(
        citySelect.value,
        yearSelect.value,
        monthSelect.value
      );
      meta.innerHTML = `<span class="text-sand">${escapeHtml(data.city.name)}</span> · ${escapeHtml(data.month_label)} ${data.year} · ${data.days.length} kun`;
      if (source) {
        source.href = data.link;
        source.textContent = `aladhan.com/calendar/${data.city.city}/${data.city.country}`;
      }
      const featured =
        data.days.find((d) => d.gregorian_iso === todayIso()) || data.days[0];
      renderToday(featured);
      renderRows(data.days);
    } catch (err) {
      meta.textContent = "Xatolik";
      body.innerHTML =
        '<tr><td colspan="8" class="px-4 py-10 text-center text-red-300">Namoz vaqtlarini yuklab bo‘lmadi.</td></tr>';
      console.error(err);
    }
  }

  function persist() {
    localStorage.setItem("namoz_city", citySelect.value);
    localStorage.setItem("namoz_year", yearSelect.value);
    localStorage.setItem("namoz_month", monthSelect.value);
    load();
  }

  [citySelect, yearSelect, monthSelect].forEach((el) =>
    el.addEventListener("change", persist)
  );
  load();
})();
