import './bootstrap';
import 'bootstrap';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import './firebase/firebase-listener';
import Choices from "choices.js";
import "choices.js/public/assets/styles/choices.min.css";
import { Grid, html } from "gridjs";
import "gridjs/dist/theme/mermaid.css";
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control.min.js';
import 'bootstrap-table/dist/extensions/custom-view/bootstrap-table-custom-view.min.js';
import 'bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js';


window.Choices = Choices;
window.Grid = Grid;
window.html = html;
window.bootstrap = bootstrap;
window.jQuery = $;
window.$ = $;


let app_url = document.querySelector('meta[name="app-url"]').getAttribute('content');
let api_url = document.querySelector('meta[name="api-url"]').getAttribute('content');
window.app_url=app_url;
window.api_url=api_url;


document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement; // 👈 html
    const toggleButton = document.getElementById("themeToggle");
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');

    function applyThemeColor(isLight) {
        metaThemeColor?.setAttribute(
            "content",
            isLight ? "#3b5df6" : "#0b0b18"
        );
    }

    const isLight = root.classList.contains("theme-light");
    applyThemeColor(isLight);

    toggleButton?.addEventListener("click", () => {
        root.classList.toggle("theme-light");
        const light = root.classList.contains("theme-light");

        localStorage.setItem("theme", light ? "light" : "dark");
        applyThemeColor(light);
    });
});

function showAlert(
    title,
    message,
    subtitle = "",
    status = "success" // success | danger | warning | info
) {
    const toastEl = document.getElementById("liveToast");

    const titleEl = toastEl.querySelector("#alertTitle");
    const messageEl = toastEl.querySelector("#alertMessage");
    const subtitleEl = toastEl.querySelector("#alertSubtitle");
    const iconEl = toastEl.querySelector("#alertIcon");

    // Map status → classes & icons
    const statusMap = {
        success: {
            class: 'text-bg-success',
            icon: 'fa-solid fa-circle-check'
        },
        danger: {
            class: 'text-bg-danger',
            icon: 'fa-solid fa-circle-xmark'
        },
        warning: {
            class: 'text-bg-warning',
            icon: 'fa-solid fa-triangle-exclamation'
        },
        info: {
            class: 'text-bg-info',
            icon: 'fa-solid fa-circle-info'
        }
    };

    const current = statusMap[status] || statusMap.success;

    // Clean previous bg classes
    toastEl.classList.remove(
        'text-bg-success',
        'text-bg-danger',
        'text-bg-warning',
        'text-bg-info'
    );

    // Apply new classes
    toastEl.classList.add(current.class);

    // Header & body background sync
    toastEl.querySelector('.toast-header').className = `toast-header ${current.class}`;
    toastEl.querySelector('.toast-body').className = `toast-body ${current.class} rounded`;
    toastEl.querySelector('.toast-header a').className = `${current.class} me-2`;

    // Content
    titleEl.textContent = title;
    messageEl.textContent = message;
    subtitleEl.textContent = subtitle;
    iconEl.className = current.icon;

    const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
    toast.show();
}

window.showAlert=showAlert;

function setButtonLoading(button, isLoading) {
    if (isLoading) {
        const originalWidth = button.offsetWidth;
        if (!button.dataset.originalText) {
            button.dataset.originalText = button.textContent.trim();
        }
        button.innerHTML = `
        <div class="row g-3 justify-content-center align-items-center">
            <div class="col-auto">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>
            <div id='text-loading' class="col-auto">
                <small class= btn-text">Procesando...</small>
            </div>
        </div>
        `;
        button.disabled = true;
        requestAnimationFrame(() => {
            const newWidth = button.offsetWidth;
            const small = button.querySelector("#text-loading");
            if (small && newWidth > originalWidth) {
                small.classList.add("d-none"); // hide text
            }
        });
    } else {
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
        button.disabled = false;
    }
}
window.setButtonLoading=setButtonLoading;

document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebar-overlay');
    toggleBtn?.addEventListener('click', function () {
        document.body.classList.toggle('sb-sidenav-toggled');
        if(document.querySelector("body").classList.contains("sb-sidenav-toggled")){
            overlay.classList.remove("d-none");
        }else{
            overlay.classList.add("d-none");

        }
    });

    overlay.addEventListener('click', function () {
        document.body.classList.remove('sb-sidenav-toggled');
            overlay.classList.add("d-none");

    });
});

function getEntryIcon(type) {
    switch (type) {

        /* ===== ENTRY TYPES ===== */
        case 'opening_balance':
            return '<i class="fa-solid fa-circle-play text-primary"></i>'

        case 'opening_balance_credit':
            return '<i class="fa-solid fa-circle-play text-danger"></i>'

        case 'transfer':
            return '<i class="fa-solid fa-right-left text-warning"></i>'

        case 'asset_acquisition':
            return '<i class="fa-solid fa-building text-info"></i>'

        /* ===== ACCOUNT TYPES ===== */
        case 'asset':
            return '<i class="fa-solid fa-box-archive text-primary"></i>'

        case 'liability':
            return '<i class="fa-solid fa-scale-balanced text-danger"></i>'

        case 'equity':
            return '<i class="fa-solid fa-chart-pie text-success"></i>'

        case 'income':
            return '<i class="fa-solid fa-arrow-trend-up text-success"></i>'

        case 'expense':
            return '<i class="fa-solid fa-arrow-trend-down text-danger"></i>'

        default:
            return '<i class="fa-solid fa-file-lines text-muted"></i>'
    }
}
function isMobile() {
    return window.matchMedia('(max-width: 768px)').matches;
}

function formatMoney(value) {
    return (Math.round(value * 100) / 100).toFixed(2);
}
function formatTextClass(value) {
    if(value == 0){
        return "text-muted";
    }else if(value >= 0){
        return "text-success";
    }else{
        return "text-danger";
    }
}
function formatCurrency(value) {
    return value.toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN'
    });
}

window.formatMoney=formatMoney;
window.formatTextClass=formatTextClass;
window.formatCurrency=formatCurrency;
window.isMobile=isMobile;

window.getEntryIcon=getEntryIcon;