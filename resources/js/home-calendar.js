import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from "@fullcalendar/timegrid";
import itLocale from "@fullcalendar/core/locales/it";

document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById("home-weekly-calendar");
    if (!calendarEl) return;

    const eventsUrl = calendarEl.dataset.eventsUrl;

    const calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin],
        initialView: "timeGridWeek",
        locale: itLocale,
        firstDay: 1,
        height: "auto",
        contentHeight: "auto",
        slotMinTime: "06:00:00",
        slotMaxTime: "22:00:00",
        headerToolbar: {
            left: "title",
            center: "",
            right: "",
        },
        buttonText: {
            today: "Oggi",
        },
        events: eventsUrl
            ? {
                  url: eventsUrl,
                  failure(error) {
                      console.error("Errore nel caricamento eventi", error);
                  },
              }
            : [],
        displayEventTime: true,
        dayMaxEvents: true,
        nowIndicator: true,
    });

    calendar.render();
});
