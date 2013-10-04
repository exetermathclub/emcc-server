$(function () {
    "use strict";
    var fields = [$("#username"), $("#email"), $("#realname"), $("#password"), $("#confirm"), $("#team"), $("#address")],
        tips = [$("#username_tip"),
                $("#email_tip"),
                $("#realname_tip"),
                $("#password_tip"),
                $("#confirm_tip"),
                $("#team_tip"),
                $("#address_tip")],
        username_el = $("#username"),
        email_el    = $("#email"),
        realname_el = $("#realname"),
        password_el = $("#password"),
        team_el     = $("#team"),
        address_el  = $("#address"),
        button_el   = $("#button");

    function verify() {
        var i, ret = true;
        for (i = 0; i < 7; i += 1) {
            if (i === 4) {
                if (fields[4].val() !== fields[3].val()) {
                    tips[4].text("Passwords do not match.");
                    ret = false;
                } else {
                    tips[4].text("");
                }
            } else if (fields[i].val().length === 0 && i !== 5) {
                tips[i].text("This field is required.");
                ret = false;
            } else {
                tips[i].text("");
            }
        }
        return ret;
    }

    // Register a user with the given data, then redirect to login
    function register(username, email, realname, password, orgname, address) {
        $.ajax({
            url: "../wsgi-scripts/register.py",
            method: "POST",
            data: {
                "username": username,
                "email": email,
                "realname": realname,
                "password": CryptoJS.SHA512(password).toString(CryptoJS.enc.Hex),
                "orgname": orgname,
                "address": address
            },
            dataType: "text",
            success: function (data) {
                if (data === "true") {
                    window.location.href = "login.shtml";
                } else if (data === "false") {
                    username_el.val("");
                    $("#username_tip").text("This username has already been used, please choose another one");
                }
            }
        });
    }

    button_el.click(function () {
        if (verify()) {
            register(username_el.val(), email_el.val(), realname_el.val(), password_el.val(), team_el.val(), address_el.val());
        }
    });

    password_el.keypress(function (e) {
        if (e && e.keyCode === 13) {
            button_el.click();
        }
    });

});
