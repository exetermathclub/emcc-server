$(function () {
    "use strict";
    var fields = [$("#username"), $("#email"), $("#realname"), $("#password"), $("#confirm"), $("#team")],
        tips = [$("#username_tip"),
                $("#email_tip"),
                $("#realname_tip"),
                $("#password_tip"),
                $("#confirm_tip"),
                $("#team_tip")],
        username_el = $("#username"),
        email_el    = $("#email"),
        realname_el = $("#realname"),
        password_el = $("#password"),
        team_el     = $("#team"),
        button_el   = $("#button");

    function verify() {
        var i, ret = true;
        for (i = 0; i < 6; i += 1) {
            if (i === 4 && fields[4].val() !== fields[3].val()) {
                tips[4].text("Passwords do not match.");
                ret = false;
            } else if (i === 1 && fields[1].val().indexOf("@") === -1) {
                tips[1].text("Please enter a valid email address.");
                ret = false;
            } else if (i !== 5 && fields[i].val().length === 0) {
                tips[i].text("This field is required.");
                ret = false;
            } else {
                tips[i].text("");
            }
        }
        return ret;
    }

    // Register a user with the given data, then redirect to login
    function register(username, email, realname, password, orgname) {
        $.ajax({
            url: "../wsgi-scripts/register.py",
            method: "POST",
            data: {
                "username": username,
                "email": email,
                "realname": realname,
                "password": CryptoJS.SHA512(password).toString(CryptoJS.enc.Hex),
                "orgname": orgname
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
            register(username_el.val(), email_el.val(), realname_el.val(), password_el.val(), team_el.val());
        }
    });

    password_el.keypress(function (e) {
        if (e && e.keyCode === 13) {
            button_el.click();
        }
    });

});
