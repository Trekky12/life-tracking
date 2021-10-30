"use strict";

const loadingIconTimesheetNotice = document.querySelector("#loadingIconTimesheetNotice");
const timesheetNoticeForm = document.querySelector("#timesheetNoticeForm");
const timesheetNotice = timesheetNoticeForm.querySelector("#inputNotice");

// Get notice
fetch(jsObject.timesheets_sheets_notice_data, {
    method: "GET",
    credentials: "same-origin",
})
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {
        loadingIconTimesheetNotice.classList.add("hidden");
        timesheetNoticeForm.classList.remove("hidden");

        if (data.status !== "error" && data.entry) {
            timesheetNotice.value = data.entry.notice;
        }
    });

timesheetNoticeForm.addEventListener("submit", function (e) {
    e.preventDefault();

    let alertError = document.querySelector("#alertError");
    let alertErrorDetail = alertError.querySelector("#alertErrorDetail");

    alertError.classList.add("hidden");
    alertErrorDetail.innerHTML = "";

    document.getElementById("loading-overlay").classList.remove("hidden");

    var data = {};
    data["notice"] = timesheetNotice.value;

    getCSRFToken()
        .then(function (token) {
            data["csrf_name"] = token.csrf_name;
            data["csrf_value"] = token.csrf_value;

            return fetch(timesheetNoticeForm.action, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data["status"] === "success") {
                allowedReload = true;
                window.location.reload();
            } else {
                document.getElementById("loading-overlay").classList.add("hidden");
                alertErrorDetail.innerHTML = data["message"];
                alertError.classList.remove("hidden");
            }
        })
        .catch(function (error) {
            console.log(error);
            document.getElementById("loading-overlay").classList.add("hidden");
            alertErrorDetail.innerHTML = lang.request_error;
            alertError.classList.remove("hidden");
        });
});
