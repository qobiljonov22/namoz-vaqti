(() => {
  const loginPanel = document.getElementById("login-panel");
  const dashPanel = document.getElementById("dash-panel");
  const loginForm = document.getElementById("login-form");
  const loginError = document.getElementById("login-error");
  const logoutBtn = document.getElementById("logout-btn");
  const yearSelect = document.getElementById("admin-year");
  const citySelect = document.getElementById("admin-city");
  const body = document.getElementById("admin-body");
  const meta = document.getElementById("admin-meta");
  const reloadBtn = document.getElementById("reload-btn");
  const exportBtn = document.getElementById("export-btn");
  const modal = document.getElementById("edit-modal");
  const editForm = document.getElementById("edit-form");
  const editLabel = document.getElementById("edit-label");
  const editIso = document.getElementById("edit-iso");
  const editImsak = document.getElementById("edit-imsak");
  const editFajr = document.getElementById("edit-fajr");
  const editMaghrib = document.getElementById("edit-maghrib");
  const editIsha = document.getElementById("edit-isha");

  if (!loginPanel || !dashPanel) return;

  let currentDays = [];

  function showUI() {
    const ok = AdminStore.isLoggedIn();
    loginPanel.classList.toggle("hidden", ok);
    dashPanel.classList.toggle("hidden", !ok);
    if (ok) load();
  }

  function openEdit(day) {
    editIso.value = day.gregorian_iso;
    editLabel.textContent = `${day.ramadan_day}-kun · ${day.gregorian} · ${day.weekday}`;
    editImsak.value = day.imsak;
    editFajr.value = day.fajr;
    editMaghrib.value = day.maghrib;
    editIsha.value = day.isha;
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }

  function closeEdit() {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }

  function renderRows(days) {
    body.innerHTML = days
      .map((day) => {
        const badge = day._overridden
          ? '<span class="ml-2 rounded bg-gold/20 px-1.5 py-0.5 text-[10px] uppercase text-gold">edit</span>'
          : "";
        return `
          <tr class="hover:bg-sage/20 ${day._overridden ? "bg-gold/5" : ""}">
            <td class="px-3 py-3 font-medium">${escapeHtml(day.ramadan_day)}${badge}</td>
            <td class="px-3 py-3 text-mist">${escapeHtml(day.gregorian)}</td>
            <td class="px-3 py-3 text-gold font-semibold">${escapeHtml(day.imsak)}</td>
            <td class="px-3 py-3">${escapeHtml(day.fajr)}</td>
            <td class="px-3 py-3 text-gold font-semibold">${escapeHtml(day.maghrib)}</td>
            <td class="px-3 py-3">${escapeHtml(day.isha)}</td>
            <td class="px-3 py-3">
              <button type="button" data-iso="${escapeHtml(day.gregorian_iso)}"
                class="edit-btn rounded-lg border border-sage/50 px-3 py-1.5 text-xs hover:border-gold hover:text-gold">
                Edit
              </button>
            </td>
          </tr>
        `;
      })
      .join("");

    body.querySelectorAll(".edit-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        const day = currentDays.find((d) => d.gregorian_iso === btn.dataset.iso);
        if (day) openEdit(day);
      });
    });
  }

  async function load() {
    meta.textContent = "Yuklanmoqda…";
    body.innerHTML =
      '<tr><td colspan="7" class="px-4 py-10 text-center text-mist">Yuklanmoqda…</td></tr>';
    try {
      const year = Number(yearSelect.value);
      const cityId = citySelect.value;
      const data = await Aladhan.ramadanCalendar(cityId, year);
      const map = await AdminStore.getMergedOverrides();
      currentDays = AdminStore.applyOverrides(data.days, year, cityId, map);
      const edited = currentDays.filter((d) => d._overridden).length;
      meta.innerHTML = `<span class="text-sand">${escapeHtml(data.city.name)}</span> · ${data.days.length} kun · override: ${edited}`;
      renderRows(currentDays);
    } catch (err) {
      meta.textContent = "Xatolik";
      body.innerHTML =
        '<tr><td colspan="7" class="px-4 py-10 text-center text-red-300">Yuklab bo‘lmadi.</td></tr>';
      console.error(err);
    }
  }

  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const user = document.getElementById("login-user").value.trim();
    const pass = document.getElementById("login-pass").value;
    if (AdminStore.login(user, pass)) {
      loginError.classList.add("hidden");
      showUI();
    } else {
      loginError.classList.remove("hidden");
    }
  });

  logoutBtn.addEventListener("click", () => {
    AdminStore.logout();
    showUI();
  });

  editForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const year = Number(yearSelect.value);
    const cityId = citySelect.value;
    const iso = editIso.value;
    const key = AdminStore.overrideKey(year, cityId, iso);
    const map = AdminStore.getLocalOverrides();
    map[key] = {
      imsak: editImsak.value.trim(),
      fajr: editFajr.value.trim(),
      maghrib: editMaghrib.value.trim(),
      isha: editIsha.value.trim(),
    };
    AdminStore.setLocalOverrides(map);
    closeEdit();
    load();
  });

  document.getElementById("edit-cancel").addEventListener("click", closeEdit);
  document.getElementById("edit-reset").addEventListener("click", () => {
    const year = Number(yearSelect.value);
    const cityId = citySelect.value;
    const key = AdminStore.overrideKey(year, cityId, editIso.value);
    const map = AdminStore.getLocalOverrides();
    delete map[key];
    AdminStore.setLocalOverrides(map);
    closeEdit();
    load();
  });

  exportBtn.addEventListener("click", () => {
    const map = AdminStore.getLocalOverrides();
    const blob = new Blob([JSON.stringify(map, null, 2)], { type: "application/json" });
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "overrides.json";
    a.click();
    URL.revokeObjectURL(a.href);
  });

  reloadBtn.addEventListener("click", load);
  yearSelect.addEventListener("change", load);
  citySelect.addEventListener("change", load);

  fillCitySelect(citySelect, "tashkent");
  showUI();
})();
