$(function () {
    "use strict";
    var password_tip = $("#password_tip"),
        confirm_tip = $("#confirm_tip"),
        password_el = $("#password"),
        confirm_el = $("#confirm"),
        submit_el = $("#submit"),
        qs = {},
        queries;

    queries = window.location.search.substring(1).split("&key=");
    qs.username = queries[0].substring(9);
    qs.key = queries[1];

    $("#username").text(qs.username);

    function verify() {
        var ret = true;
        if (password_el.val().length === 0) {
            password_tip.text("This field is required");
            ret = false;
        } else {
            password_tip.text("");
        }
        if (confirm_el.val() !== password_el.val()) {
            confirm_tip.text("Please enter the same password as above.");
            ret = false;
        } else {
            confirm_tip.text("");
        }
        return ret;
    }

    function reset(username, key, password) {
        $.ajax({
            url: "../wsgi-scripts/pwchange.py",
            method: "POST",
            data: {
                "username": username,
                "key": key,
                "password": CryptoJS.SHA512(password).toString(CryptoJS.enc.Hex)
            },
            dataType: "json",
            success: function () {
                window.location.href = "login.shtml";
            }
        });
    }

    submit_el.click(function () {
        if (verify()) {
            reset(qs.username, qs.key, password_el.val());
        }
    });

    confirm_el.keypress(function (e) {
        if (e && e.keyCode === 13) {
            submit_el.click();
        }
    });
});
