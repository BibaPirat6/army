import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import multiMonthPlugin from "@fullcalendar/multimonth";
import interactionPlugin from "@fullcalendar/interaction";
import ruLocale from "@fullcalendar/core/locales/ru";

// Vue компоненты
import { createApp } from "vue";
import StructureGraph from "./components/StructureGraph.vue";

import { initTomSelect } from "./components/tom-select";

const vueApp = createApp({});
vueApp.component("structure-graph", StructureGraph);
vueApp.mount("#app");

let calendar = null;

document.addEventListener("DOMContentLoaded", function () {
  initTomSelect("#position_type_id");
  initTomSelect("#chief_type_id");
  initTomSelect("#commissariat_id");
  initTomSelect('#department_id');

  const calendarEl = document.getElementById("calendar");
  if (!calendarEl) return;

  calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, multiMonthPlugin, interactionPlugin],
    initialView: "multiMonthYear",
    locale: ruLocale,
    firstDay: 1,
    editable: false,

    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "multiMonthYear,dayGridMonth",
    },

    buttonText: {
      today: "Сегодня",
      month: "Месяц",
      multiMonthYear: "Год",
    },

    events: "/calendar/events",

    // Клик по дате - переход на создание задачи
    dateClick: function (info) {
      window.location.href =
        "/calendar/tasks/create?start_date=" + info.dateStr;
    },

    // Клик по задаче - переход на просмотр
    eventClick: function (info) {
      window.location.href = "/calendar/tasks/" + info.event.id;
    },
  });

  calendar.render();
});

// Функции для модалки статистики (если используются)
window.openStatsModal = function () {
  const modal = document.getElementById("statsModal");
  if (modal) modal.classList.remove("hidden");
};

window.closeStatsModal = function () {
  const modal = document.getElementById("statsModal");
  if (modal) modal.classList.add("hidden");
};
