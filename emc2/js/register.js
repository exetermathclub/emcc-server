$(function () {
    "use strict";
    var fields = [$("#username"), $("#realname"), $("#password"), $("#confirm"), $("#team"), $("#address")],
        tips = [$("#username_tip"),
                $("#realname_tip"),
                $("#password_tip"),
                $("#confirm_tip"),
                $("#team_tip"),
                $("#address_tip")],
        it,
        username_el = $("#username"),
        realname_el = $("#realname"),
        password_el = $("#password"),
        team_el     = $("#team"),
        address_el  = $("#address"),
        button_el   = $("#button");

    function verify(obj) {
        var last, i, ret = true;
        for (i = 0; i < 6; i += 1) {
            if (fields[i][0] === obj) {
                last = i;
                break;
            }
        }
        for (i = 0; i <= last; i += 1) {
            if (i === 3) {
                if (fields[3].val() !== fields[2].val()) {
                    tips[3].text("Passwords do not match.");
                    ret = false;
                } else {
                    tips[3].text("");
                }
            } else if (i !== 4 && fields[i].val().length === 0) {
                tips[i].text("This field is required.");
                ret = false;
            } else if (i == 0 && !(/^[a-zA-Z0-9-_\.]*$/.test(fields[i].val()))) {
                tips[i].text("Can't contain special characters.");
                ret = false;
            } else {
                tips[i].text("");
            }
        }
        return ret;
    }

    function wrapper(event) {
        verify(event.target);
    }

    for (it = 0; it < 5; it += 1) {
        fields[it].keyup(wrapper);
    }

    // Register a user with the given data, then redirect to login
    function register(username, realname, password, orgname, address) {
        $.ajax({
            url: "../wsgi-scripts/register.py",
            method: "POST",
            data: {
                "username": username,
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
        if (verify(document.getElementById("address"))) {
            var body_el = $("body"),
                initial = body_el.css("cursor");
            body_el.css("cursor", "wait");
            button_el.attr("disabled", "disabled");
            register(username_el.val(), realname_el.val(), password_el.val(), team_el.val(), address_el.val());
            button_el.removeAttr("disabled");
            body_el.css("cursor", initial);
        }
    });

    password_el.keypress(function (e) {
        if (e && e.keyCode == 13) {
            button_el.click();
        }
    });

});
