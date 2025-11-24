import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import itLocale from "@fullcalendar/core/locales/it";
import axios from "axios";

const modal = document.getElementById("weekly-schedule-modal");
const modalDaySelect = document.getElementById("weekly-modal-day-select");
const modalHourStart = document.getElementById("weekly-modal-hour-start");
const modalHourEnd = document.getElementById("weekly-modal-hour-end");
const modalType = document.getElementById("weekly-modal-type");
const modalSave = document.getElementById("weekly-modal-save");
const modalCancel = document.getElementById("weekly-modal-cancel");
const modalDelete = document.getElementById("weekly-modal-delete");
const modalTitle = modal?.querySelector("[data-modal-title]");

const fallbackColor = "#94a3b8";

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

const createAttendanceHelpers = (container) => {
    const attendanceTypes = parseJson(container.dataset.attendanceTypes, []);
    const map = new Map(attendanceTypes.map((type) => [String(type.id), type]));
    const defaultAttendanceTypeId =
        container.dataset.defaultAttendanceType ||
        (attendanceTypes.length ? String(attendanceTypes[0].id) : null);
    const colorMap = new Map(attendanceTypes.map((type) => [String(type.id), type.color || fallbackColor]));

    const getType = (id) => map.get(String(id));
    const getLabel = (id) => {
        const attendanceType = getType(id);
        return attendanceType ? attendanceType.name : "Fascia";
    };
    const getBadge = (id) => {
        const attendanceType = getType(id);
        if (!attendanceType) return "Fascia";
        return attendanceType.acronym ? `${attendanceType.name} (${attendanceType.acronym})` : attendanceType.name;
    };
    const getColor = (id) => {
        const key = String(id ?? "default");
        if (!colorMap.has(key)) {
            colorMap.set(key, fallbackColor);
        }
        return colorMap.get(key) || fallbackColor;
    };

    return { defaultAttendanceTypeId, getLabel, getBadge, getColor };
};

const applyEventAppearance = (event, helpers) => {
    const color = helpers.getColor(event.extendedProps.attendanceTypeId);
    event.setProp("backgroundColor", color);
    event.setProp("borderColor", color);
    event.setProp("textColor", "#1f2937");
};

const renderSummary = (container, calendar) => {
    const summaryEl = container.querySelector(".weekly-summary");
    if (!summaryEl) return;

    const helpers = container.__attendanceHelpers;
    if (!helpers) return;
    const emptyText = container.dataset.emptyText || "";
    const events = calendar
        .getEvents()
        .filter((event) => !event.extendedProps.timeOff)
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
            const typeLabel = helpers.getBadge(event.extendedProps.attendanceTypeId);
            const color = helpers.getColor(event.extendedProps.attendanceTypeId);

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

    return calendar
        .getEvents()
        .filter((event) => !event.extendedProps.timeOff)
        .map((event) => ({
            day: weekdayMap[event.start.getDay()],
            date: formatDateLocal(event.start),
            hour_start: normalizeTime(event.start.toTimeString()),
            hour_end: normalizeTime(event.end.toTimeString()),
            attendance_type_id: event.extendedProps.attendanceTypeId || helpers.defaultAttendanceTypeId,
        }));
};

let activeCalendar = null;
let activeContainer = null;
let selectedEvent = null;

const openModal = ({ calendar, container, event = null, start = null, end = null, attendanceTypeId = null }) => {
    activeCalendar = calendar;
    activeContainer = container;
    selectedEvent = event;

    const helpers = container.__attendanceHelpers;
    if (!helpers) return;
    const baseDate = event ? event.start : start;
    const addLabel = container.dataset.labelAdd || "Aggiungi fascia";
    const editLabel = container.dataset.labelEdit || "Modifica fascia";
    if (modalTitle) {
        modalTitle.textContent = event ? `${editLabel} - ${container.dataset.userName}` : `${addLabel} - ${container.dataset.userName}`;
    }

    if (modalDaySelect && baseDate) {
        modalDaySelect.value = weekdayMap[baseDate.getDay()];
    }
    if (modalHourStart) modalHourStart.value = normalizeTime((event ? event.start : start)?.toTimeString() || "08:00");
    if (modalHourEnd) modalHourEnd.value = normalizeTime((event ? event.end : end)?.toTimeString() || "12:00");
    if (modalType) {
        const value = event ? event.extendedProps.attendanceTypeId : attendanceTypeId ?? helpers.defaultAttendanceTypeId;
        modalType.value = value ? String(value) : "";
    }

    modal?.showModal();
};

const initializeScheduler = (container) => {
    const calendarEl = container.querySelector(".user-weekly-calendar");
    if (!calendarEl) return;

    const helpers = createAttendanceHelpers(container);
    container.__attendanceHelpers = helpers;
    const readOnly = container.dataset.readonly === "true";

    const schedules = parseJson(container.dataset.schedules, []);
    const timeOff = parseJson(container.dataset.timeOff, []);
    const weekStart = new Date(`${container.dataset.weekStart}T00:00:00`);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 7);

    const weekdayShortLabels = parseJson(container.dataset.weekdayShortLabels, {});

    const toEvents = () =>
        schedules.map((item) => {
            const baseDate = item.date
                ? new Date(`${item.date}T00:00:00`)
                : new Date(weekStart.getTime() + (dayOffsets[item.day] ?? 0) * 86400000);
            const dateStr = formatDateLocal(baseDate);
            const start = `${dateStr}T${normalizeTime(item.hour_start)}`;
            const end = `${dateStr}T${normalizeTime(item.hour_end)}`;
            const attendanceTypeId = item.attendance_type_id ?? helpers.defaultAttendanceTypeId;

            return {
                title: helpers.getBadge(attendanceTypeId),
                start,
                end,
                display: "block",
                extendedProps: {
                    attendanceTypeId,
                },
            };
        });

    const toTimeOffEvents = () =>
        timeOff.map((item) => {
            const start = item.start ? new Date(item.start) : new Date(`${item.date}T00:00:00`);
            const end = item.end ? new Date(item.end) : (() => {
                const tmp = new Date(start);
                tmp.setHours(23, 59, 59, 999);
                return tmp;
            })();

            return {
                title: item.title || "Time Off",
                start,
                end,
                allDay: false,
                display: "background",
                overlap: false,
                backgroundColor: item.color || fallbackColor,
                borderColor: item.color || fallbackColor,
                extendedProps: {
                    timeOff: true,
                },
            };
        });

    const calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin, interactionPlugin],
        initialView: "timeGridWeek",
        initialDate: container.dataset.weekStart,
        validRange: { start: weekStart, end: weekEnd },
        allDaySlot: false,
        slotMinTime: "06:00:00",
        slotMaxTime: "22:00:00",
        selectable: !readOnly,
        editable: false,
        locale: itLocale,
        height: "auto",
        contentHeight: 360,
        headerToolbar: false,
        dayHeaderContent: (arg) =>
            weekdayShortLabels[weekdayMap[arg.date.getDay()]] || weekdayMap[arg.date.getDay()].slice(0, 3),
        events: [...toEvents(), ...toTimeOffEvents()],
        select: (info) => {
            if (readOnly) return;
            openModal({ calendar, container, start: info.start, end: info.end, attendanceTypeId: helpers.defaultAttendanceTypeId });
            calendar.unselect();
        },
        eventClick: (info) => {
            if (readOnly || info.event.extendedProps.timeOff) return;
            openModal({ calendar, container, event: info.event });
        },
        eventDidMount: (info) => {
            if (info.event.extendedProps.timeOff) return;
            applyEventAppearance(info.event, helpers);
        },
    });

    calendar.render();
    renderSummary(container, calendar);

    const addBtn = container.querySelector(".add-slot");
    if (addBtn && !readOnly) {
        addBtn.addEventListener("click", () => {
            const start = new Date(weekStart);
            start.setHours(9, 0, 0, 0);
            const end = new Date(weekStart);
            end.setHours(12, 0, 0, 0);
            openModal({ calendar, container, start, end, attendanceTypeId: helpers.defaultAttendanceTypeId });
        });
    }

    const saveBtn = container.querySelector(".save-weekly-schedule");
    saveBtn?.addEventListener("click", () => {
        if (readOnly) return;
        const saveUrl = container.dataset.saveUrl;
        const schedule = serializeEvents(calendar, helpers);
        const form = new FormData();
        form.append("user_id", container.dataset.userId || "");
        form.append("week_start", container.dataset.weekStart || "");

        schedule.forEach((item, index) => {
            form.append(`schedule[${index}][day]`, item.day);
            form.append(`schedule[${index}][date]`, item.date);
            form.append(`schedule[${index}][hour_start]`, item.hour_start);
            form.append(`schedule[${index}][hour_end]`, item.hour_end);
            form.append(`schedule[${index}][attendance_type_id]`, item.attendance_type_id);
        });

        saveBtn.disabled = true;
        saveBtn.classList.add("loading");

        const successMessage = container.dataset.successMessage || "Calendario salvato con successo.";
        const successRedirect = container.dataset.successRedirect || "";

        axios
            .post(saveUrl, form)
            .then(() => {
                setNavStatus(container.dataset.userId, true);
                if (successMessage) alert(successMessage);
                if (successRedirect) {
                    window.location.href = successRedirect;
                }
            })
            .catch(() => alert(container.dataset.errorSave || "Errore nel salvataggio del calendario."))
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.classList.remove("loading");
            });
    });
};

modalSave?.addEventListener("click", () => {
    if (!activeCalendar || !activeContainer) return;

    const helpers = activeContainer.__attendanceHelpers;
    if (!helpers || activeContainer.dataset.readonly === "true") return;
    const selectedDay = modalDaySelect ? modalDaySelect.value : null;
    if (!selectedDay || !(selectedDay in dayOffsets)) return;

    const baseDate = new Date(`${activeContainer.dataset.weekStart}T00:00:00`);
    baseDate.setDate(baseDate.getDate() + dayOffsets[selectedDay]);

    const newStart = combineDateTime(baseDate, modalHourStart.value);
    const newEnd = combineDateTime(baseDate, modalHourEnd.value);

    if (newEnd <= newStart) {
        alert(activeContainer.dataset.errorEnd || "L'orario di fine deve essere successivo all'inizio.");
        return;
    }

    const newTypeId = modalType?.value || helpers.defaultAttendanceTypeId;
    const newTitle = helpers.getBadge(newTypeId);

    if (selectedEvent) {
        selectedEvent.setStart(newStart);
        selectedEvent.setEnd(newEnd);
        selectedEvent.setProp("title", newTitle);
        selectedEvent.setExtendedProp("attendanceTypeId", newTypeId);
        applyEventAppearance(selectedEvent, helpers);
    } else {
        const event = activeCalendar.addEvent({
            title: newTitle,
            start: newStart,
            end: newEnd,
            display: "block",
            extendedProps: {
                attendanceTypeId: newTypeId,
            },
        });
        if (event) applyEventAppearance(event, helpers);
    }

    renderSummary(activeContainer, activeCalendar);
    selectedEvent = null;
    modal?.close();
});

modalDelete?.addEventListener("click", () => {
    if (!activeCalendar || !activeContainer || !selectedEvent) return;

    selectedEvent.remove();
    renderSummary(activeContainer, activeCalendar);
    selectedEvent = null;
    modal?.close();
});

modalCancel?.addEventListener("click", () => {
    selectedEvent = null;
    modal?.close();
});

const initWeeklySchedulers = (root = document) => {
    root.querySelectorAll(".user-weekly-scheduler").forEach((container) => {
        initializeScheduler(container);
    });
};

document.addEventListener("DOMContentLoaded", () => initWeeklySchedulers());
window.initWeeklySchedulers = initWeeklySchedulers;
