import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";

document.addEventListener("DOMContentLoaded", function () {
    const calendarEl = document.getElementById("calendar");
    if (!calendarEl) return;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin],
        initialView: "dayGridMonth",
        locale: "it",
        events: "/standard/overtime-requests/list",
        eventClick: function (info) {
            window.location.href = `/standard/overtime-requests/${info.event.id}`;
        },
    });
    calendar.render();
});
