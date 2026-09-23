/**
 * Static admin store — Netlify / Vercel / local (WordPresssiz).
 * Overrides: localStorage + optional /data/overrides.json (barcha foydalanuvchilar uchun).
 */
window.AdminStore = {
  KEY: "ramazon_admin_session",
  DATA_KEY: "ramazon_overrides",
  USER: "admin",
  PASS: "admin123",

  isLoggedIn() {
    try {
      return sessionStorage.getItem(this.KEY) === "1";
    } catch {
      return false;
    }
  },

  login(user, pass) {
    if (user === this.USER && pass === this.PASS) {
      sessionStorage.setItem(this.KEY, "1");
      return true;
    }
    return false;
  },

  logout() {
    sessionStorage.removeItem(this.KEY);
  },

  /** @returns {Record<string, object>} key = `${year}|${cityId}|${iso}` */
  getLocalOverrides() {
    try {
      return JSON.parse(localStorage.getItem(this.DATA_KEY) || "{}") || {};
    } catch {
      return {};
    }
  },

  setLocalOverrides(map) {
    localStorage.setItem(this.DATA_KEY, JSON.stringify(map));
  },

  overrideKey(year, cityId, iso) {
    return `${year}|${cityId}|${iso}`;
  },

  async fetchPublicOverrides() {
    try {
      const res = await fetch("/data/overrides.json", { cache: "no-store" });
      if (!res.ok) return {};
      const data = await res.json();
      return data && typeof data === "object" ? data : {};
    } catch {
      return {};
    }
  },

  async getMergedOverrides() {
    const pub = await this.fetchPublicOverrides();
    const local = this.getLocalOverrides();
    return { ...pub, ...local };
  },

  applyOverrides(days, year, cityId, map) {
    return days.map((day) => {
      const key = this.overrideKey(year, cityId, day.gregorian_iso);
      const o = map[key];
      if (!o) return day;
      return {
        ...day,
        imsak: o.imsak ?? day.imsak,
        fajr: o.fajr ?? day.fajr,
        maghrib: o.maghrib ?? day.maghrib,
        isha: o.isha ?? day.isha,
        _overridden: true,
      };
    });
  },
};
