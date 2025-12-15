import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import itLocale from "@fullcalendar/core/locales/it";
import axios from "axios";

const calendarEl = document.getElementById("scheduled-time-off-calendar");

const parseJson = (value, fallback) => {
    try {
        return JSON.parse(value || "") || fallback;
    } catch (e) {
        return fallback;
    }
};

if (calendarEl) {
    const schedules = parseJson(calendarEl.dataset.schedules, []);
    const timeOffTypes = parseJson(calendarEl.dataset.timeOffTypes, []);
    const defaultTimeOffTypeId =
        calendarEl.dataset.defaultTimeOffType ||
        (timeOffTypes.length ? String(timeOffTypes[0].id) : null);
    const timeOffTypeMap = new Map(
        timeOffTypes.map((type) => [String(type.id), type])
    );
    const fallbackColor = "#fbbf24";

    const getType = (id) => timeOffTypeMap.get(String(id));
    const getTypeLabel = (id) => getType(id)?.name || "Ferie/permesso";
    const getColorForType = (id) => getType(id)?.color || fallbackColor;

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
            const offset = dayOffsets[item.weekday] ?? 0;
            const date = new Date(weekStart);
            date.setDate(weekStart.getDate() + offset);
            const dateStr = formatDateLocal(date);
            const startTime = normalizeTime(item.hour_from);
            const endTime = normalizeTime(item.hour_to);
            const typeId = item.time_off_type_id ?? defaultTimeOffTypeId;

            return {
                title: getTypeLabel(typeId),
                start: `${dateStr}T${startTime}`,
                end: `${dateStr}T${endTime}`,
                display: "block",
                extendedProps: {
                    timeOffTypeId: typeId,
                },
            };
        });

    const saveButton = document.getElementById("save-scheduled-time-off");
    const addSlotButton = document.getElementById("add-timeoff-slot");
    const modal = document.getElementById("scheduled-time-off-modal");
    const modalDaySelect = document.getElementById("scheduled-modal-day-select");
    const modalHourStart = document.getElementById("scheduled-modal-hour-start");
    const modalHourEnd = document.getElementById("scheduled-modal-hour-end");
    const modalType = document.getElementById("scheduled-modal-type");
    const modalSave = document.getElementById("scheduled-modal-save");
    const modalCancel = document.getElementById("scheduled-modal-cancel");
    const modalDelete = document.getElementById("scheduled-modal-delete");
    const toast = document.getElementById("scheduled-time-off-toast");

    const weekdayMap = [
        "sunday",
        "monday",
        "tuesday",
        "wednesday",
        "thursday",
        "friday",
        "saturday",
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

    const updateEventAppearance = (event) => {
        const color = getColorForType(event.extendedProps.timeOffTypeId);
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

    const openModal = ({
        event = null,
        start = null,
        end = null,
        timeOffTypeId = defaultTimeOffTypeId,
    }) => {
        selectedEvent = event;
        const baseDate = event ? event.start : start;

        const title = document.querySelector("#scheduled-time-off-modal .modal-box h3");
        if (title) {
            title.textContent = event
                ? modal?.dataset.titleEdit || "Modifica fascia"
                : modal?.dataset.titleAdd || "Aggiungi fascia";
        }

        if (modalDaySelect && baseDate) {
            modalDaySelect.value = weekdayMap[baseDate.getDay()];
        }
        modalHourStart.value = normalizeTime(
            (event ? event.start : start)?.toTimeString() || "09:00"
        );
        modalHourEnd.value = normalizeTime(
            (event ? event.end : end)?.toTimeString() || "11:00"
        );
        if (modalType) {
            modalType.value = String(
                event ? event.extendedProps.timeOffTypeId : timeOffTypeId ?? defaultTimeOffTypeId
            );
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
            openModal({ start: info.start, end: info.end, timeOffTypeId: defaultTimeOffTypeId });
            calendar.unselect();
        },
        eventClick: (info) => openModal({ event: info.event }),
        eventDidMount: (info) => updateEventAppearance(info.event),
    });

    calendar.render();

    const serializeEvents = () => {
        return calendar.getEvents().map((event) => ({
            weekday: weekdayMap[event.start.getDay()],
            hour_from: normalizeTime(event.start.toTimeString()),
            hour_to: normalizeTime(event.end.toTimeString()),
            time_off_type_id: event.extendedProps.timeOffTypeId || defaultTimeOffTypeId,
        }));
    };

    const saveSchedule = () => {
        const schedule = serializeEvents();
        const form = new FormData();

        schedule.forEach((item, index) => {
            form.append(`schedule[${index}][weekday]`, item.weekday);
            form.append(`schedule[${index}][hour_from]`, item.hour_from);
            form.append(`schedule[${index}][hour_to]`, item.hour_to);
            form.append(`schedule[${index}][time_off_type_id]`, item.time_off_type_id);
        });

        axios
            .post(saveUrl, form)
            .then(() => {
                showToast();
            })
            .catch((error) => {
                const message =
                    modal?.dataset.errorSave ||
                    error.response?.data?.message ||
                    "Errore nel salvataggio.";
                alert(message);
            });
    };

    const applyModalChanges = () => {
        const day = modalDaySelect.value || "monday";
        const startTime = modalHourStart.value || "09:00";
        const endTime = modalHourEnd.value || "11:00";
        const typeId = modalType?.value || defaultTimeOffTypeId;

        const selectedDayOffset = dayOffsets[day] ?? 0;
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
            selectedEvent.setProp("title", getTypeLabel(typeId));
            updateEventAppearance(selectedEvent);
        } else {
            const event = calendar.addEvent({
                title: getTypeLabel(typeId),
                start: newStart,
                end: newEnd,
                display: "block",
                extendedProps: {
                    timeOffTypeId: typeId,
                },
            });
            updateEventAppearance(event);
        }

        modal?.close();
    };

    if (saveButton) {
        saveButton.addEventListener("click", saveSchedule);
    }

    if (addSlotButton) {
        addSlotButton.addEventListener("click", () => {
            const mondayDate = new Date(weekStart);
            const start = combineDateTime(mondayDate, "09:00");
            const end = combineDateTime(mondayDate, "11:00");
            openModal({ start, end, timeOffTypeId: defaultTimeOffTypeId });
        });
    }

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
            modal?.close();
        });
    }
}
