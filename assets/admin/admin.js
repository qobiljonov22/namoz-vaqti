(function () {
  const sidebar = document.getElementById("ramazon-sidebar");
  const overlay = document.getElementById("ramazon-sidebar-overlay");
  const title = document.getElementById("ramazon-sidebar-title");
  if (!sidebar || !overlay) return;

  const fields = [
    "id",
    "year",
    "city_id",
    "city_name",
    "ramadan_day",
    "gregorian_date",
    "gregorian_label",
    "weekday",
    "imsak",
    "fajr",
    "maghrib",
    "isha",
    "notes",
  ];

  function openSidebar(mode) {
    sidebar.classList.add("is-open");
    overlay.classList.add("is-open");
    sidebar.setAttribute("aria-hidden", "false");
    if (title) {
      title.textContent = mode === "edit" ? "Tahrirlash" : "Yangi kun";
    }
  }

  function closeSidebar() {
    sidebar.classList.remove("is-open");
    overlay.classList.remove("is-open");
    sidebar.setAttribute("aria-hidden", "true");
  }

  function fillForm(data) {
    fields.forEach(function (key) {
      const el = document.getElementById("field-" + key);
      if (!el) return;
      el.value = data && data[key] != null ? data[key] : key === "id" ? "0" : "";
    });
    const qadr = document.getElementById("field-is_qadr");
    if (qadr) qadr.checked = !!(data && Number(data.is_qadr));
    syncCityName();
  }

  function resetForm() {
    fillForm({
      id: 0,
      year: 2027,
      city_id: "tashkent",
      city_name: "Toshkent",
      ramadan_day: 1,
      gregorian_date: "",
      gregorian_label: "",
      weekday: "",
      imsak: "",
      fajr: "",
      maghrib: "",
      isha: "",
      notes: "",
      is_qadr: 0,
    });
  }

  function syncCityName() {
    const select = document.getElementById("field-city_id");
    const hidden = document.getElementById("field-city_name");
    if (!select || !hidden) return;
    const opt = select.options[select.selectedIndex];
    if (opt) hidden.value = opt.getAttribute("data-name") || opt.textContent.trim();
  }

  document.querySelectorAll(".ramazon-edit-link").forEach(function (link) {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      let data = {};
      try {
        data = JSON.parse(link.getAttribute("data-item") || "{}");
      } catch (err) {
        data = {};
      }
      fillForm(data);
      openSidebar("edit");
    });
  });

  document.querySelectorAll(".ramazon-open-sidebar").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      resetForm();
      openSidebar("new");
    });
  });

  document.querySelectorAll(".ramazon-sidebar-close").forEach(function (btn) {
    btn.addEventListener("click", closeSidebar);
  });

  overlay.addEventListener("click", closeSidebar);

  const citySelect = document.getElementById("field-city_id");
  if (citySelect) citySelect.addEventListener("change", syncCityName);

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeSidebar();
  });

  // URL orqali ochilgan bo'lsa
  if (sidebar.classList.contains("is-open")) {
    openSidebar(document.getElementById("field-id").value > 0 ? "edit" : "new");
  }
})();
