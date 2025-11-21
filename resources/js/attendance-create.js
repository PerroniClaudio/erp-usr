import axios from "axios";

const scheduleContainer = document.querySelector("[data-expected-schedule]");

if (scheduleContainer) {
    const listEl = scheduleContainer.querySelector(
        "[data-expected-schedule-list]"
    );
    const dateInput = document.querySelector('input[name="date"]');
    const userSelect = document.querySelector('select[name="user_id"]');
    const fetchUrl = scheduleContainer.dataset.fetchUrl;
    const emptyLabel = scheduleContainer.dataset.emptyLabel || "";
    const fallbackColor = scheduleContainer.dataset.fallbackColor || "#94a3b8";

    const parseSchedule = (raw) => {
        try {
            return Array.isArray(raw) ? raw : JSON.parse(raw || "[]");
        } catch {
            return [];
        }
    };

    const renderSchedule = (items) => {
        if (!listEl) return;

        if (!items.length) {
            listEl.innerHTML = `<li class="text-xs text-base-content/60">${emptyLabel}</li>`;
            return;
        }

        listEl.innerHTML = items
            .map((item) => {
                const type = item.attendance_type || {};
                const label = `${type.name} (${type.acronym})` || "-";
                const color = type.color || fallbackColor;
                const start = item.hour_start || "--:--";
                const end = item.hour_end || "--:--";

                return `
                    <li class="flex items-center justify-between rounded-lg border-4 border-base-100 bg-base-100 px-2 py-1">
                        <div class="flex items-center gap-2 text-xs uppercase text-base-content/60">
                            <span class="inline-block w-2.5 h-2.5 rounded-full border border-base-300" style="background-color: ${color};"></span>
                            ${label}
                        </div>
                        <span class="font-medium">${start} - ${end}</span>
                    </li>
                `;
            })
            .join("");
    };

    const loadSchedule = () => {
        if (!fetchUrl || !dateInput?.value) {
            renderSchedule([]);
            return;
        }

        const params = new URLSearchParams({ date: dateInput.value });
        if (userSelect && userSelect.value) {
            params.append("user_id", userSelect.value);
        }

        axios
            .get(`${fetchUrl}?${params.toString()}`)
            .then((response) => renderSchedule(response.data.schedule || []))
            .catch(() => renderSchedule([]));
    };

    const initialSchedule = parseSchedule(
        scheduleContainer.dataset.initialSchedule
    );
    renderSchedule(initialSchedule);

    dateInput?.addEventListener("change", loadSchedule);
    userSelect?.addEventListener("change", loadSchedule);
}
