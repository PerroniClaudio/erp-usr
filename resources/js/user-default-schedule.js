import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import itLocale from "@fullcalendar/core/locales/it";
import axios from "axios";

const calendarEl = document.getElementById("default-schedule-calendar");

if (calendarEl) {
    const schedules = JSON.parse(calendarEl.dataset.schedules || "[]");
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

            return {
                title: item.type === "overtime" ? "Straordinario" : "Lavoro",
                start: `${dateStr}T${startTime}`,
                end: `${dateStr}T${endTime}`,
                type: item.type,
                display: "block",
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

    const weekdayMap = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];
    const weekdayLabels = [
        calendarEl.dataset.sundayLabel || "Domenica",
        calendarEl.dataset.mondayLabel || "Lunedì",
        calendarEl.dataset.tuesdayLabel || "Martedì",
        calendarEl.dataset.wednesdayLabel || "Mercoledì",
        calendarEl.dataset.thursdayLabel || "Giovedì",
        calendarEl.dataset.fridayLabel || "Venerdì",
        calendarEl.dataset.saturdayLabel || "Sabato",
    ];
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
    let pendingRange = null;

    const updateEventAppearance = (event) => {
        const type = event.extendedProps.type || "work";
        const isOvertime = type === "overtime";
        const bg = isOvertime ? "#facc15" : "#60a5fa"; // amber-300 / blue-400
        const border = isOvertime ? "#d97706" : "#2563eb"; // amber-600 / blue-600
        const text = "#1f2937"; // slate-800

        event.setProp("backgroundColor", bg);
        event.setProp("borderColor", border);
        event.setProp("textColor", text);
    };

    const combineDateTime = (date, timeStr) => {
        const [h, m] = timeStr.split(":").map((v) => parseInt(v, 10) || 0);
        const result = new Date(date);
        result.setHours(h, m, 0, 0);
        return result;
    };

    const openModal = ({ event = null, start = null, end = null, type = "work" }) => {
        selectedEvent = event;
        pendingRange = event ? null : { start, end, type };
        const baseDate = event ? event.start : start;

        const title = document.querySelector("#schedule-modal .modal-box h3");
        if (title) {
            title.textContent = event
                ? modal?.dataset.titleEdit || "Modifica fascia"
                : modal?.dataset.titleAdd || "Aggiungi fascia";
        }
        if (modalDaySelect) {
            modalDaySelect.value = weekdayMap[baseDate.getDay()];
        }
        modalHourStart.value = (event ? event.start : start).toTimeString().slice(0, 5);
        modalHourEnd.value = (event ? event.end : end).toTimeString().slice(0, 5);
        modalType.value = event ? event.extendedProps.type || "work" : type;
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
        slotMinTime: "06:00:00",
        slotMaxTime: "22:00:00",
        selectable: true,
        editable: true,
        locale: itLocale,
        headerToolbar: false,
        dayHeaderContent: (arg) => weekdayShortLabels[arg.date.getDay()],
        events: mapSchedulesToEvents(),
        select: (info) => {
            openModal({ start: info.start, end: info.end, type: "work" });
            calendar.unselect();
        },
        eventClick: (info) => openModal({ event: info.event }),
        eventDidMount: (info) => updateEventAppearance(info.event),
    });

    calendar.render();

    const serializeEvents = () => {
        return calendar.getEvents().map((event) => ({
            day: weekdayMap[event.start.getDay()],
            hour_start: event.start.toTimeString().slice(0, 5),
            hour_end: event.end.toTimeString().slice(0, 5),
            type: event.extendedProps.type || "work",
        }));
    };

    const saveSchedule = () => {
        const schedule = serializeEvents();
        const form = new FormData();

        schedule.forEach((item, index) => {
            form.append(`schedule[${index}][day]`, item.day);
            form.append(`schedule[${index}][hour_start]`, item.hour_start);
            form.append(`schedule[${index}][hour_end]`, item.hour_end);
            form.append(`schedule[${index}][type]`, item.type);
        });

        axios
            .post(saveUrl, form)
            .then(() => window.location.reload())
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

        openModal({ start, end, type: "work" });
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

        const newType = modalType.value;

        if (selectedEvent) {
            selectedEvent.setStart(newStart);
            selectedEvent.setEnd(newEnd);
            selectedEvent.setExtendedProp("type", newType);
            selectedEvent.setProp(
                "title",
                newType === "overtime"
                    ? (modal?.dataset.typeOvertime || "Straordinario")
                    : (modal?.dataset.typeWork || "Lavoro")
            );
            updateEventAppearance(selectedEvent);
        } else {
            const added = calendar.addEvent({
                title:
                    newType === "overtime"
                        ? (modal?.dataset.typeOvertime || "Straordinario")
                        : (modal?.dataset.typeWork || "Lavoro"),
                start: newStart,
                end: newEnd,
                type: newType,
                display: "block",
            });
            if (added) updateEventAppearance(added);
        }

        pendingRange = null;
        modal?.close();
    });

    modalCancel?.addEventListener("click", () => {
        selectedEvent = null;
        pendingRange = null;
        modal?.close();
    });

    modalDelete?.addEventListener("click", () => {
        if (selectedEvent) {
            selectedEvent.remove();
            selectedEvent = null;
            modal?.close();
        }
    });
}
