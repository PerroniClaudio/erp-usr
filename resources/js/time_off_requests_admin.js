import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import itLocale from "@fullcalendar/core/locales/it";
import axios from "axios";

let calendarEl = document.getElementById("calendar");
const companyFilter = document.getElementById("company_filter");
const groupsFilter = document.getElementById("groups_filter");
const typesFilter = document.getElementById("time_off_type_filter");

const onFilterChange = () => {
    console.log("Filter changed");
    if (calendarEl) {
        renderCalendar();
    }
};

[companyFilter, groupsFilter, typesFilter].forEach((filter) => {
    if (filter) {
        filter.addEventListener("change", onFilterChange);
    }
});

if (calendarEl) {
    renderCalendar();
}

function renderCalendar() {
    let calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin],
        initialView: "dayGridMonth",
        locale: itLocale,
        width: "auto",
        contentHeight: "auto",
        handleWindowResize: true,
        events: function (fetchInfo, successCallback, failureCallback) {
            let page = new Date(fetchInfo.start).getMonth() + 1; // Use the month as the page number

            let params = new URLSearchParams({
                page: page,
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
                group_id: groupsFilter ? groupsFilter.value : "",
                company_id: companyFilter ? companyFilter.value : "",
                type_id: typesFilter ? typesFilter.value : "",
            });

            fetch(`/admin/time-off-requests/list?${params}`)
                .then((response) => response.json())
                .then((data) => {
                    successCallback(data.events); // Assuming the API returns an `events` array
                })
                .catch((error) => {
                    failureCallback(error);
                });
        },
        eventClick: function (info) {
            window.location.href = `/admin/time-off-requests/${info.event.groupId}`;
        },
    });
    calendar.render();
}
