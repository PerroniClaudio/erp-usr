import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import itLocale from "@fullcalendar/core/locales/it";
import axios from "axios";

const modal = document.getElementById("weekly-timeoff-modal");
const modalDaySelect = document.getElementById("weekly-timeoff-day");
const modalHourStart = document.getElementById("weekly-timeoff-hour-start");
const modalHourEnd = document.getElementById("weekly-timeoff-hour-end");
const modalType = document.getElementById("weekly-timeoff-type");
const modalSave = document.getElementById("weekly-timeoff-save");
const modalCancel = document.getElementById("weekly-timeoff-cancel");
const modalDelete = document.getElementById("weekly-timeoff-delete");
const modalTitle = modal?.querySelector("[data-modal-title]");

const fallbackColor = "#fbbf24";

const weekdayMap = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];
const dayOffsets = {
    monday: 0,
    tuesday: 1,
    wednesday: 2,
    thursday: 3,
    friday: 4,
    saturday: 5,
    sunday: 6,
};

const normalizeTime = (value) => (value || "").toString().slice(0, 5);
const formatDateLocal = (date) =>
    [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, "0"),
        String(date.getDate()).padStart(2, "0"),
    ].join("-");

const combineDateTime = (date, timeStr) => {
    const [h, m] = timeStr.split(":").map((v) => parseInt(v, 10) || 0);
    const result = new Date(date);
    result.setHours(h, m, 0, 0);
    return result;
};

const parseJson = (value, fallback) => {
    try {
        return JSON.parse(value || "") || fallback;
    } catch (_) {
        return fallback;
    }
};

const createTypeHelpers = (container) => {
    const types = parseJson(container.dataset.timeoffTypes, []);
    const map = new Map(types.map((type) => [String(type.id), type]));
    const defaultTypeId =
        container.dataset.defaultTimeoffType || (types.length ? String(types[0].id) : null);

    const getType = (id) => map.get(String(id));
    const getLabel = (id) => getType(id)?.name || "Assenza";
    const getColor = (id) => getType(id)?.color || fallbackColor;

    return { defaultTypeId, getType, getLabel, getColor };
};

const applyEventAppearance = (event, helpers) => {
    const color = helpers.getColor(event.extendedProps.timeOffTypeId);
    event.setProp("backgroundColor", color);
    event.setProp("borderColor", color);
    event.setProp("textColor", "#1f2937");
};

const renderSummary = (container, calendar) => {
    const summaryEl = container.querySelector(".weekly-summary");
    if (!summaryEl) return;

    const helpers = container.__typeHelpers;
    if (!helpers) return;
    const emptyText = container.dataset.emptyText || "";
    const events = calendar
        .getEvents()
        .slice()
        .sort((a, b) => {
            const dayA = dayOffsets[weekdayMap[a.start.getDay()]] ?? 7;
            const dayB = dayOffsets[weekdayMap[b.start.getDay()]] ?? 7;
            if (dayA !== dayB) return dayA - dayB;
            return a.start.getTime() - b.start.getTime();
        });

    if (!events.length) {
        summaryEl.innerHTML = `<p class="text-xs text-base-content/60">${emptyText}</p>`;
        return;
    }

    const weekdayLabels = parseJson(container.dataset.weekdayLabels, {});

    const rows = events
        .map((event) => {
            const dayKey = weekdayMap[event.start.getDay()];
            const label = weekdayLabels[dayKey] || dayKey;
            const start = normalizeTime(event.start.toTimeString());
            const end = normalizeTime(event.end.toTimeString());
            const typeLabel = helpers.getLabel(event.extendedProps.timeOffTypeId);
            const color = helpers.getColor(event.extendedProps.timeOffTypeId);

            return `
                <div class="flex items-center justify-between rounded-lg bg-base-100 border border-base-200 px-2 py-1">
                    <div class="flex flex-col">
                        <span class="text-xs uppercase text-base-content/60 flex items-center gap-1">
                            <span class="inline-block w-2.5 h-2.5 rounded-full border border-base-300" style="background-color: ${color};"></span>
                            ${label}
                        </span>
                        <span>${start} - ${end}</span>
                    </div>
                    <span class="badge badge-outline text-[11px]">${typeLabel}</span>
                </div>
            `;
        })
        .join("");

    summaryEl.innerHTML = rows;
};

const serializeEvents = (calendar, helpers) => {
    if (!helpers) return [];

    return calendar.getEvents().map((event) => ({
        weekday: weekdayMap[event.start.getDay()],
        date: formatDateLocal(event.start),
        hour_from: normalizeTime(event.start.toTimeString()),
        hour_to: normalizeTime(event.end.toTimeString()),
        time_off_type_id: event.extendedProps.timeOffTypeId || helpers.defaultTypeId,
    }));
};

let activeCalendar = null;
let activeContainer = null;
let selectedEvent = null;

const openModal = ({ calendar, container, event = null, start = null, end = null, typeId = null }) => {
    activeCalendar = calendar;
    activeContainer = container;
    selectedEvent = event;

    const helpers = container.__typeHelpers;
    if (!helpers) return;
    const baseDate = event ? event.start : start;
    const addLabel = container.dataset.labelAdd || "Aggiungi";
    const editLabel = container.dataset.labelEdit || "Modifica";
    if (modalTitle) {
        modalTitle.textContent = event
            ? `${editLabel} - ${container.dataset.userName}`
            : `${addLabel} - ${container.dataset.userName}`;
    }

    if (modalDaySelect && baseDate) {
        modalDaySelect.value = weekdayMap[baseDate.getDay()];
    }
    if (modalHourStart) modalHourStart.value = normalizeTime((event ? event.start : start)?.toTimeString() || "09:00");
    if (modalHourEnd) modalHourEnd.value = normalizeTime((event ? event.end : end)?.toTimeString() || "11:00");
    if (modalType) {
        const value = event ? event.extendedProps.timeOffTypeId : typeId ?? helpers.defaultTypeId;
        modalType.value = value ? String(value) : "";
    }

    modal?.showModal();
};

const initCalendar = (container) => {
    const weekStart = new Date(`${container.dataset.weekStart}T00:00:00`);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 7);

    const typeHelpers = createTypeHelpers(container);
    container.__typeHelpers = typeHelpers;

    const schedules = parseJson(container.dataset.schedules, []);
    const existing = parseJson(container.dataset.existing, []);

    const mapSchedulesToEvents = () =>
        schedules.map((item) => {
            const offset = dayOffsets[item.weekday] ?? 0;
            const date = new Date(weekStart);
            date.setDate(weekStart.getDate() + offset);
            const dateStr = formatDateLocal(date);
            const startTime = normalizeTime(item.hour_from);
            const endTime = normalizeTime(item.hour_to);
            const typeId = item.time_off_type_id ?? typeHelpers.defaultTypeId;

            return {
                title: typeHelpers.getLabel(typeId),
                start: `${dateStr}T${startTime}`,
                end: `${dateStr}T${endTime}`,
                display: "block",
                extendedProps: {
                    timeOffTypeId: typeId,
                },
            };
        });

    const calendarEl = container.querySelector(".user-weekly-calendar");
    if (!calendarEl) return;

    const weekdayShortLabels = parseJson(container.dataset.weekdayShortLabels, []);

    const calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin, interactionPlugin],
        initialView: "timeGridWeek",
        initialDate: weekStart,
        validRange: {
            start: weekStart,
            end: weekEnd,
        },
        allDaySlot: false,
        slotMinTime: "07:00:00",
        slotMaxTime: "19:00:00",
        selectable: true,
        editable: true,
        locale: itLocale,
        headerToolbar: false,
        dayHeaderContent: (arg) => weekdayShortLabels[arg.date.getDay()] || "",
        events: mapSchedulesToEvents(),
        select: (info) => {
            openModal({ calendar, container, start: info.start, end: info.end, typeId: typeHelpers.defaultTypeId });
            calendar.unselect();
        },
        eventClick: (info) => openModal({ calendar, container, event: info.event }),
        eventDidMount: (info) => applyEventAppearance(info.event, typeHelpers),
    });

    existing.forEach((entry) => {
        const offset = dayOffsets[entry.weekday?.toLowerCase()] ?? 0;
        const date = new Date(weekStart);
        date.setDate(weekStart.getDate() + offset);
        const dateStr = formatDateLocal(date);
        const start = `${dateStr}T${normalizeTime(entry.hour_from)}`;
        const end = `${dateStr}T${normalizeTime(entry.hour_to)}`;

        const event = calendar.addEvent({
            title: entry.time_off_type_label || typeHelpers.getLabel(entry.time_off_type_id),
            start,
            end,
            display: "block",
            extendedProps: {
                timeOffTypeId: entry.time_off_type_id,
            },
        });
        applyEventAppearance(event, typeHelpers);
    });

    calendar.render();
    renderSummary(container, calendar);

    const addBtn = container.querySelector(".add-slot");
    const saveBtn = container.querySelector(".save-weekly-timeoff");

    if (addBtn) {
        addBtn.addEventListener("click", () => {
            const mondayDate = new Date(weekStart);
            const start = combineDateTime(mondayDate, "09:00");
            const end = combineDateTime(mondayDate, "11:00");
            openModal({ calendar, container, start, end, typeId: typeHelpers.defaultTypeId });
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener("click", () => {
            const schedule = serializeEvents(calendar, typeHelpers);
            const form = new FormData();
            form.append("user_id", container.dataset.userId);
            form.append("week_start", formatDateLocal(weekStart));

            schedule.forEach((item, index) => {
                form.append(`schedule[${index}][weekday]`, item.weekday);
                form.append(`schedule[${index}][date]`, item.date);
                form.append(`schedule[${index}][hour_from]`, item.hour_from);
                form.append(`schedule[${index}][hour_to]`, item.hour_to);
                form.append(`schedule[${index}][time_off_type_id]`, item.time_off_type_id);
            });

            axios
                .post(container.dataset.saveUrl, form)
                .then(() => {
                    renderSummary(container, calendar);
                    setNavStatus(container.dataset.userId, true);
                })
                .catch((error) => {
                    const message =
                        modal?.dataset.errorSave ||
                        error.response?.data?.message ||
                        "Errore nel salvataggio.";
                    alert(message);
                });
        });
    }

    container.__calendar = calendar;
};

const setNavStatus = (userId, isComplete) => {
    const button = document.querySelector(`[data-user-nav="${userId}"]`);
    if (!button) return;
    button.dataset.hasExisting = isComplete ? "1" : "0";
    const dot = button.querySelector("[data-status-dot]");
    if (!dot) return;
    dot.classList.toggle("bg-success/80", isComplete);
    dot.classList.toggle("ring-success/10", isComplete);
    dot.classList.toggle("bg-error/80", !isComplete);
    dot.classList.toggle("ring-error/10", !isComplete);
};

const applyModalChanges = () => {
    if (!activeCalendar || !activeContainer) return;
    const helpers = activeContainer.__typeHelpers;
    if (!helpers) return;

    const day = modalDaySelect.value || "monday";
    const startTime = modalHourStart.value || "09:00";
    const endTime = modalHourEnd.value || "11:00";
    const typeId = modalType?.value || helpers.defaultTypeId;

    const selectedDayOffset = dayOffsets[day] ?? 0;
    const weekStart = new Date(`${activeContainer.dataset.weekStart}T00:00:00`);
    const baseDate = new Date(weekStart);
    baseDate.setDate(weekStart.getDate() + selectedDayOffset);
    const baseDateStr = formatDateLocal(baseDate);

    const newStart = combineDateTime(new Date(`${baseDateStr}T00:00:00`), startTime);
    const newEnd = combineDateTime(new Date(`${baseDateStr}T00:00:00`), endTime);

    if (newEnd <= newStart) {
        const message =
            modal?.dataset.errorEndBeforeStart ||
            "L'orario di fine deve essere successivo a quello di inizio.";
        alert(message);
        return;
    }

    if (selectedEvent) {
        selectedEvent.setStart(newStart);
        selectedEvent.setEnd(newEnd);
        selectedEvent.setExtendedProp("timeOffTypeId", typeId);
        selectedEvent.setProp("title", helpers.getLabel(typeId));
        applyEventAppearance(selectedEvent, helpers);
    } else {
        const event = activeCalendar.addEvent({
            title: helpers.getLabel(typeId),
            start: newStart,
            end: newEnd,
            display: "block",
            extendedProps: {
                timeOffTypeId: typeId,
            },
        });
        applyEventAppearance(event, helpers);
    }

    renderSummary(activeContainer, activeCalendar);
    modal?.close();
};

if (modalSave) {
    modalSave.addEventListener("click", applyModalChanges);
}

if (modalCancel) {
    modalCancel.addEventListener("click", () => modal?.close());
}

if (modalDelete) {
    modalDelete.addEventListener("click", () => {
        if (selectedEvent) {
            selectedEvent.remove();
            selectedEvent = null;
        }
        renderSummary(activeContainer, activeCalendar);
        modal?.close();
    });
}

window.initWeeklyScheduledTimeOff = (rootEl) => {
    const containers = rootEl.querySelectorAll(".user-weekly-timeoff");
    containers.forEach((container) => {
        initCalendar(container);
    });
};
