if (!window.JSON) {
    window.JSON = {
        stringify: function (vContent) {
            if (vContent instanceof Object) {
                var sOutput = "";
                if (vContent.constructor === Array) {
                    for (var nId = 0; nId < vContent.length; sOutput += this.stringify(vContent[nId]) + ",", nId++){};
                    return "[" + sOutput.substr(0, sOutput.length - 1) + "]";
                }
                if (vContent.toString !== Object.prototype.toString) { return "\"" + vContent.toString().replace(/"/g, "\\$&") + "\""; }
                for (var sProp in vContent) { sOutput += "\"" + sProp.replace(/"/g, "\\$&") + "\":" + this.stringify(vContent[sProp]) + ","; }
                return "{" + sOutput.substr(0, sOutput.length - 1) + "}";
            }
            return typeof vContent === "string" ? "\"" + vContent.replace(/"/g, "\\$&") + "\"" : String(vContent);
        }
    };
}
$(function () {
    "use strict";
    var tip = $("#login_tip"),
        username_el = $("#username"),
        password_el = $("#password"),
        button_el = $("#button"),
        dialog_el = $("#dialog"),
        cover_el = $("#cover"),
        dialog_input_el = $("#dialog_input"),
        submit_el = $("#dialog_submit"),
        body_el = $("body");

    // Login with given username-password pair
    function login(username, password) {
        if (username === "admin") {
            //If we're logging in as an admin, we go through the SRP protocol
            body_el.addClass("loading");
            var rand = generateA();
            $.ajax({
                url: "../wsgi-scripts/admin_auth.py",
                method: "POST",
                data: {
                    "A": bigInt2str(rand.A, 16)
                },
                dataType: "json",
                success: function (data) {
                    var kdict = generateKey({
                        "uname": "admin",
                        "password": password,
                        "a": rand.a,
                        "A": rand.A
                    }, {
                        "B": data.B,
                        "salt": data.s
                    });
                    $.ajax({
                        url: "../wsgi-scripts/admin_validate.py",
                        method: "GET",
                        data: {
                            "message": JSON.stringify(encrypt(kdict.K, "SRP_CLIENT_SUCCESS_MESSAGE")),
                            "id": data.id
                        },
                        success: function (validation) {
                            if (validation.message !== null && decrypt(kdict.K, JSON.parse(validation.message)) === "SRP_SERVER_SUCCESS_MESSAGE") {
                                localStorage.SRP_SESS_KEY = kdict.K.toString(CryptoJS.enc.Base64);
                                localStorage.SRP_ID = data.id;
                                location.href = "admin.shtml";
                            }
                        }
                    });
                }
            });
            body_el.removeClass("loading");
        } else {
            $.ajax({
                url: "../wsgi-scripts/authenticate.py",
                method: "POST",
                data: {
                    "username": username,
                    "password": CryptoJS.SHA512(password).toString(CryptoJS.enc.Hex)
                },
                dataType: "json",
                success: function (data) {
                    if (data.correct) {
                        window.location.href = "dashboard.shtml";
                    } else {
                        tip.text("Incorrect username or password.");
                        username_el.focus();
                    }
                }
            });
        }
    }

    // Login button click handler
    button_el.click(function () {
        login(username_el.val(), password_el.val());
    });

    password_el.keyup(function (e) {
        if (e && e.keyCode === 13) {
            button_el.click();
        }
    });

    function forget_submit(username) {
        $.ajax({
            url: "../wsgi-scripts/resetemail.py",
            method: "GET",
            data: {
                "username": username
            },
            success: function () {
                $("#prompt").text("We have sent the password reset information to the email you registered with, please check your email.");
                body_el.removeClass("loading");
                submit_el.removeAttr("disabled");
                submit_el.text("Resend");
            },
            error: function () {
                body_el.removeClass("loading");
                dialog_input_el.css("background-color", "yellow");
            }
        });
    }

    $("#forgot").click(function () {
        dialog_el.show();
        cover_el.show();
        dialog_input_el.focus();
    });

    cover_el.click(function () {
        $(this).hide();
        dialog_el.hide();
    });

    submit_el.click(function () {
        if (dialog_input_el.val() !== "") {
            $(this).attr("disabled", "");
            body_el.addClass("loading");
            forget_submit(dialog_input_el.val());
        } else {
            dialog_input_el.css("background-color", "yellow");
        }
    });

    dialog_input_el.keyup(function (e) {
        submit_el.removeAttr("disabled");
        if (e && e.keyCode === 13) {
            submit_el.click();
        }
    });

});
