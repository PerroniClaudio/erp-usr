import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import itLocale from "@fullcalendar/core/locales/it";

let calendarEl = document.getElementById("calendar");
let calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin],
    initialView: "dayGridMonth",
    locale: itLocale,
    width: "auto",
    contentHeight: "auto",
    handleWindowResize: true,
    events: function (fetchInfo, successCallback, failureCallback) {
        let page = new Date(fetchInfo.start).getMonth() + 1; // Use the month as the page number
        fetch(
            `/standard/attendances/user?page=${page}&start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`
        )
            .then((response) => response.json())
            .then((data) => {
                successCallback(data.events); // Assuming the API returns an `events` array
            })
            .catch((error) => {
                failureCallback(error);
            });
    },
    eventClick: function (info) {
        window.location.href = `/standard/attendances/${info.event.id}/edit`;
    },
});
calendar.render();
