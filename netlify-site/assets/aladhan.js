window.CITIES = [
  { id: "tashkent", name: "Toshkent", city: "Tashkent", country: "Uzbekistan" },
  { id: "samarkand", name: "Samarqand", city: "Samarkand", country: "Uzbekistan" },
  { id: "bukhara", name: "Buxoro", city: "Bukhara", country: "Uzbekistan" },
  { id: "andijan", name: "Andijon", city: "Andijan", country: "Uzbekistan" },
  { id: "namangan", name: "Namangan", city: "Namangan", country: "Uzbekistan" },
  { id: "fergana", name: "Farg'ona", city: "Fergana", country: "Uzbekistan" },
  { id: "nukus", name: "Nukus", city: "Nukus", country: "Uzbekistan" },
  { id: "khiva", name: "Xiva", city: "Khiva", country: "Uzbekistan" },
  { id: "karshi", name: "Qarshi", city: "Karshi", country: "Uzbekistan" },
  { id: "termez", name: "Termiz", city: "Termez", country: "Uzbekistan" },
  { id: "jizzakh", name: "Jizzax", city: "Jizzakh", country: "Uzbekistan" },
  { id: "navoi", name: "Navoiy", city: "Navoi", country: "Uzbekistan" },
  { id: "gulistan", name: "Guliston", city: "Gulistan", country: "Uzbekistan" },
];

window.HIJRI_RAMADAN = { 2026: 1447, 2027: 1448, 2028: 1449 };

window.WEEKDAY_UZ = {
  Monday: "Dushanba",
  Tuesday: "Seshanba",
  Wednesday: "Chorshanba",
  Thursday: "Payshanba",
  Friday: "Juma",
  Saturday: "Shanba",
  Sunday: "Yakshanba",
};

window.MONTH_UZ = {
  1: "yanvar", 2: "fevral", 3: "mart", 4: "aprel",
  5: "may", 6: "iyun", 7: "iyul", 8: "avgust",
  9: "sentabr", 10: "oktabr", 11: "noyabr", 12: "dekabr",
};

window.Aladhan = {
  cleanTime(value) {
    return String(value || "").replace(/\s*\(.*\)$/, "").trim();
  },

  findCity(id) {
    return window.CITIES.find((c) => c.id === id) || window.CITIES[0];
  },

  async getJson(url) {
    const res = await fetch(url);
    const data = await res.json();
    if (!res.ok || data.code !== 200) {
      throw new Error((data && data.data) || "Aladhan API xatosi");
    }
    return data;
  },

  async ramadanCalendar(cityId, year) {
    const city = this.findCity(cityId);
    const hijriYear =
      window.HIJRI_RAMADAN[year] ||
      (await this.getJson(
        `https://api.aladhan.com/v1/islamicYearFromGregorianForRamadan/${year}`
      )).data;

    const payload = await this.getJson(
      `https://api.aladhan.com/v1/hijriCalendarByCity/${hijriYear}/9?city=${encodeURIComponent(city.city)}&country=${encodeURIComponent(city.country)}&method=3`
    );

    const days = payload.data.map((row) => {
      const g = row.date.gregorian;
      const h = row.date.hijri;
      const t = row.timings;
      const dayNum = Number(h.day);
      const holidays = h.holidays || [];
      let isQadr = holidays.some((x) => /Qadr/i.test(x));
      if ([21, 23, 25, 27, 29].includes(dayNum)) isQadr = true;
      const gMonth = Number(g.month.number);
      const gDay = Number(g.day);

      return {
        ramadan_day: dayNum,
        gregorian: `${String(gDay).padStart(2, "0")} ${window.MONTH_UZ[gMonth] || ""} ${g.year}`,
        gregorian_iso: `${g.year}-${String(gMonth).padStart(2, "0")}-${String(gDay).padStart(2, "0")}`,
        weekday: window.WEEKDAY_UZ[g.weekday.en] || g.weekday.en,
        imsak: this.cleanTime(t.Imsak),
        fajr: this.cleanTime(t.Fajr),
        maghrib: this.cleanTime(t.Maghrib),
        isha: this.cleanTime(t.Isha),
        is_first: dayNum === 1,
        is_qadr: isQadr,
      };
    });

    return {
      ok: true,
      city,
      year: Number(year),
      hijri_year: Number(hijriYear),
      days,
      link: `https://aladhan.com/ramadan-calendar/${year}`,
    };
  },

  async prayerCalendar(cityId, year, month) {
    const city = this.findCity(cityId);
    const payload = await this.getJson(
      `https://api.aladhan.com/v1/calendarByCity/${year}/${month}?city=${encodeURIComponent(city.city)}&country=${encodeURIComponent(city.country)}&method=3`
    );

    const days = payload.data.map((row) => {
      const g = row.date.gregorian;
      const t = row.timings;
      const gMonth = Number(g.month.number);
      const gDay = Number(g.day);
      return {
        day: gDay,
        gregorian: `${String(gDay).padStart(2, "0")} ${window.MONTH_UZ[gMonth] || ""} ${g.year}`,
        gregorian_iso: `${g.year}-${String(gMonth).padStart(2, "0")}-${String(gDay).padStart(2, "0")}`,
        weekday: window.WEEKDAY_UZ[g.weekday.en] || g.weekday.en,
        fajr: this.cleanTime(t.Fajr),
        sunrise: this.cleanTime(t.Sunrise),
        dhuhr: this.cleanTime(t.Dhuhr),
        asr: this.cleanTime(t.Asr),
        maghrib: this.cleanTime(t.Maghrib),
        isha: this.cleanTime(t.Isha),
      };
    });

    return {
      ok: true,
      city,
      year: Number(year),
      month: Number(month),
      month_label: window.MONTH_UZ[Number(month)] || String(month),
      days,
      link: `https://aladhan.com/calendar/${city.city}/${city.country}`,
    };
  },
};

window.escapeHtml = function (value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
};

window.todayIso = function () {
  const now = new Date();
  return [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, "0"),
    String(now.getDate()).padStart(2, "0"),
  ].join("-");
};

window.fillCitySelect = function (select, selected) {
  select.innerHTML = window.CITIES.map(
    (c) =>
      `<option value="${c.id}" ${c.id === selected ? "selected" : ""}>${c.name}</option>`
  ).join("");
};
