import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import itLocale from "@fullcalendar/core/locales/it";
import axios from "axios";

const calendarEl = document.getElementById("default-schedule-calendar");

const parseJson = (value, fallback) => {
    try {
        return JSON.parse(value || "") || fallback;
    } catch (_) {
        return fallback;
    }
};

if (calendarEl) {
    const schedules = parseJson(calendarEl.dataset.schedules, []);
    const attendanceTypes = parseJson(calendarEl.dataset.attendanceTypes, []);
    const defaultAttendanceTypeId =
        calendarEl.dataset.defaultAttendanceType ||
        (attendanceTypes.length ? String(attendanceTypes[0].id) : null);
    const attendanceTypeMap = new Map(attendanceTypes.map((type) => [String(type.id), type]));
    const fallbackColor = "#94a3b8";

    const getAttendanceType = (id) => attendanceTypeMap.get(String(id));
    const getAttendanceLabel = (id) => {
        const attendanceType = getAttendanceType(id);
        return attendanceType ? attendanceType.name : "Fascia";
    };
    const getColorForType = (id) => {
        const attendanceType = getAttendanceType(id);
        return attendanceType?.color || fallbackColor;
    };

    const saveUrl = calendarEl.dataset.saveUrl;
    const initialDate = calendarEl.dataset.initialDate;
    const weekStart = new Date(`${initialDate}T00:00:00`);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 7);

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

    const mapSchedulesToEvents = () =>
        schedules.map((item) => {
            const offset = dayOffsets[item.day] ?? 0;
            const date = new Date(weekStart);
            date.setDate(weekStart.getDate() + offset);
            const dateStr = formatDateLocal(date);
            const startTime = normalizeTime(item.hour_start);
            const endTime = normalizeTime(item.hour_end);
            const attendanceTypeId = item.attendance_type_id ?? defaultAttendanceTypeId;

            return {
                title: getAttendanceLabel(attendanceTypeId),
                start: `${dateStr}T${startTime}`,
                end: `${dateStr}T${endTime}`,
                display: "block",
                extendedProps: {
                    attendanceTypeId,
                },
            };
        });

    const saveButton = document.getElementById("save-schedule");
    const addWorkSlotButton = document.getElementById("add-work-slot");
    const modal = document.getElementById("schedule-modal");
    const modalDaySelect = document.getElementById("modal-day-select");
    const modalHourStart = document.getElementById("modal-hour-start");
    const modalHourEnd = document.getElementById("modal-hour-end");
    const modalType = document.getElementById("modal-type");
    const modalSave = document.getElementById("modal-save");
    const modalCancel = document.getElementById("modal-cancel");
    const modalDelete = document.getElementById("modal-delete");
    const toast = document.getElementById("default-schedule-toast");

    const weekdayMap = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];
    const weekdayShortLabels = [
        calendarEl.dataset.sundayShortLabel || "dom",
        calendarEl.dataset.mondayShortLabel || "lun",
        calendarEl.dataset.tuesdayShortLabel || "mar",
        calendarEl.dataset.wednesdayShortLabel || "mer",
        calendarEl.dataset.thursdayShortLabel || "gio",
        calendarEl.dataset.fridayShortLabel || "ven",
        calendarEl.dataset.saturdayShortLabel || "sab",
    ];

    let selectedEvent = null;

    const updateEventAppearance = (event) => {
        const color = getColorForType(event.extendedProps.attendanceTypeId);
        event.setProp("backgroundColor", color);
        event.setProp("borderColor", color);
        event.setProp("textColor", "#1f2937");
    };

    const showToast = () => {
        if (!toast) return;
        toast.classList.remove("hidden");
        setTimeout(() => toast.classList.add("hidden"), 3000);
    };

    const combineDateTime = (date, timeStr) => {
        const [h, m] = timeStr.split(":").map((v) => parseInt(v, 10) || 0);
        const result = new Date(date);
        result.setHours(h, m, 0, 0);
        return result;
    };

    const openModal = ({ event = null, start = null, end = null, attendanceTypeId = defaultAttendanceTypeId }) => {
        selectedEvent = event;
        const baseDate = event ? event.start : start;

        const title = document.querySelector("#schedule-modal .modal-box h3");
        if (title) {
            title.textContent = event
                ? modal?.dataset.titleEdit || "Modifica fascia"
                : modal?.dataset.titleAdd || "Aggiungi fascia";
        }

        if (modalDaySelect && baseDate) {
            modalDaySelect.value = weekdayMap[baseDate.getDay()];
        }
        modalHourStart.value = normalizeTime((event ? event.start : start)?.toTimeString() || "08:00");
        modalHourEnd.value = normalizeTime((event ? event.end : end)?.toTimeString() || "12:00");
        if (modalType) {
            modalType.value = String(event ? event.extendedProps.attendanceTypeId : attendanceTypeId ?? defaultAttendanceTypeId);
        }
        modal?.showModal();
    };

    const calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin, interactionPlugin],
        initialView: "timeGridWeek",
        initialDate,
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
        dayHeaderContent: (arg) => weekdayShortLabels[arg.date.getDay()],
        events: mapSchedulesToEvents(),
        select: (info) => {
            openModal({ start: info.start, end: info.end, attendanceTypeId: defaultAttendanceTypeId });
            calendar.unselect();
        },
        eventClick: (info) => openModal({ event: info.event }),
        eventDidMount: (info) => updateEventAppearance(info.event),
    });

    calendar.render();

    const serializeEvents = () => {
        return calendar.getEvents().map((event) => ({
            day: weekdayMap[event.start.getDay()],
            hour_start: normalizeTime(event.start.toTimeString()),
            hour_end: normalizeTime(event.end.toTimeString()),
            attendance_type_id: event.extendedProps.attendanceTypeId || defaultAttendanceTypeId,
        }));
    };

    const saveSchedule = () => {
        const schedule = serializeEvents();
        const form = new FormData();

        schedule.forEach((item, index) => {
            form.append(`schedule[${index}][day]`, item.day);
            form.append(`schedule[${index}][hour_start]`, item.hour_start);
            form.append(`schedule[${index}][hour_end]`, item.hour_end);
            form.append(`schedule[${index}][attendance_type_id]`, item.attendance_type_id);
        });

        axios
            .post(saveUrl, form)
            .then(() => {
                localStorage.setItem("defaultScheduleSaved", "1");
                window.location.reload();
            })
            .catch((error) => {
                console.error(error);
                alert(modal?.dataset.errorSave || "Errore nel salvataggio del calendario.");
            });
    };

    saveButton?.addEventListener("click", saveSchedule);

    addWorkSlotButton?.addEventListener("click", () => {
        const now = calendar.getDate();
        const start = new Date(now);
        start.setHours(8, 0, 0, 0);
        const end = new Date(now);
        end.setHours(12, 0, 0, 0);

        openModal({ start, end, attendanceTypeId: defaultAttendanceTypeId });
    });

    modalSave?.addEventListener("click", () => {
        const selectedDay = modalDaySelect ? modalDaySelect.value : null;
        if (!selectedDay || !(selectedDay in dayOffsets)) return;

        const baseDate = new Date(weekStart);
        baseDate.setDate(weekStart.getDate() + dayOffsets[selectedDay]);

        const newStart = combineDateTime(baseDate, modalHourStart.value);
        const newEnd = combineDateTime(baseDate, modalHourEnd.value);

        if (newEnd <= newStart) {
            alert(modal?.dataset.errorEndBeforeStart || "L'orario di fine deve essere successivo all'inizio.");
            return;
        }

        const newTypeId = modalType?.value || defaultAttendanceTypeId;
        const newTitle = getAttendanceLabel(newTypeId);

        if (selectedEvent) {
            selectedEvent.setStart(newStart);
            selectedEvent.setEnd(newEnd);
            selectedEvent.setProp("title", newTitle);
            selectedEvent.setExtendedProp("attendanceTypeId", newTypeId);
            updateEventAppearance(selectedEvent);
        } else {
            const event = calendar.addEvent({
                title: newTitle,
                start: newStart,
                end: newEnd,
                display: "block",
                extendedProps: {
                    attendanceTypeId: newTypeId,
                },
            });
            if (event) updateEventAppearance(event);
        }

        selectedEvent = null;
        modal?.close();
    });

    modalDelete?.addEventListener("click", () => {
        if (selectedEvent) {
            selectedEvent.remove();
            selectedEvent = null;
            modal?.close();
        }
    });

    modalCancel?.addEventListener("click", () => {
        selectedEvent = null;
        modal?.close();
    });

    if (localStorage.getItem("defaultScheduleSaved")) {
        localStorage.removeItem("defaultScheduleSaved");
        showToast();
    }
}
