$(function () {
    "use strict";
    var username_tip = $("#username_tip"),
        password_tip = $("#password_tip"),
        username_el = $("#username"),
        password_el = $("#password"),
        button_el = $("#button"),
        dialog_el = $("#dialog"),
        cover_el = $("#cover"),
        dialog_input_el = $("#dialog_input"),
        submit_el = $("#dialog_submit");

    function verify() {
        var ret = true;
        if (username_el.val().length === 0) {
            username_tip.text("This field is required");
            ret = false;
        } else {
            username_tip.text("");
        }
        if (password_el.val().length === 0) {
            password_tip.text("This field is required");
            ret = false;
        } else {
            password_tip.text("");
        }
        return ret;
    }

    // Login with given username-password pair
    function login(username, password) {
        if (username === "admin") {
            //If we're logging in as an admin, we go through the SRP protocol
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
                        username_el.val("");
                        password_el.val("");
                        username_tip.text("Incorrect username or password");
                        password_tip.text("Incorrect username or password");
                    }
                }
            });
        }
    }

    // Login button click handler
    button_el.click(function () {
        if (verify()) {
            login(username_el.val(), password_el.val());
        }
    });

    password_el.keypress(function (e) {
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
                dialog_el.text("We have sent the password reset information to your email, please check your email.");
                dialog_el.show();
                cover_el.show();
                setTimeout(function () {
                    dialog_el.hide();
                    cover_el.hide();
                    $("body").css("cursor", "");
                }, 2000);
            }
        });
    }

    $("#forgot").click(function () {
        dialog_el.show();
        cover_el.show();
    });

    cover_el.click(function () {
        $(this).hide();
        dialog_el.hide();
    });

    submit_el.click(function () {
        if (dialog_input_el.val() !== "") {
            $(this).attr("disabled", "");
            $("body").css("cursor", "wait", "important");
            forget_submit(dialog_input_el.val());
        } else {
            dialog_input_el.css("background-color", "yellow");
        }
    });

    submit_el.keypress(function (e) {
        if (e && e.keyCode === 13) {
            submit_el.click();
        }
    });

});
