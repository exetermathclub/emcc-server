$(function () {
    "use strict";
    var fields = [$("#username"), $("#password")],
        tips = [$("#username_tip"), $("#password_tip")],
        it,
        username_el = $("#username"),
        password_el = $("#password"),
        button_el = $("#button");

    function verify(obj) {
        var last, i, ret = true;
        for (i = 0; i < 2; i += 1) {
            if (fields[i][0] === obj) {
                last = i;
                break;
            }
        }
        for (i = 0; i <= last; i += 1) {
            if (fields[i].val().length === 0) {
                tips[i].text("This field is required.");
                ret = false;
            } else if (!(/^[a-zA-Z0-9-_\.]*$/.test(fields[i].val()))) {
                tips[i].text("Invalid username.");
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

    for (it = 0; it < 2; it += 1) {
        fields[it].keyup(wrapper);
    }

    // Login with given username-password pair
    function login(username, password) {
        if (username == "admin") {
            //If we're logging in as an admin, we go through the SRP protocol
            var rand = generateA();
            $.ajax({
                url: "../wsgi-scripts/admin_auth.py",
                method: "POST",
                data: {
                    "A": bigInt2str(rand.A, 16)
                },
                dataType: "json",
                success: function(data) {
                    var kdict = generateKey({
                        "uname": "admin",
                        "password": password,
                        "a": rand.a,
                        "A": rand.A
                    },
                    {
                        "B": data.B,
                        "salt": data.s
                    });
                    console.log(kdict.K.toString(CryptoJS.enc.Hex));
                    console.log(JSON.stringify(encrypt(kdict.K, "SRP_CLIENT_SUCCESS_MESSAGE")));
                    $.ajax({
                        url: "../wsgi-scripts/admin_validate.py",
                        method: "GET",
                        data: {
                            "message": JSON.stringify(encrypt(kdict.K, "SRP_CLIENT_SUCCESS_MESSAGE")),
                            "id": data.id
                        },
                        success: function(validation) {
                            if (validation.message != null && decrypt(kdict.K, JSON.parse(validation.message)) == "SRP_SERVER_SUCCESS_MESSAGE") {
                                localStorage["SRP_SESS_KEY"] = kdict.K.toString(CryptoJS.enc.Base64);
                                localStorage["SRP_ID"] = data.id;
                                location.href = "admin.shtml";
                            }
                        }
                    });
                }
            });
        }
        else {
            $.ajax({
                url: "../wsgi-scripts/authenticate.py",
                method: "POST",
                data: {
                    "username": username,
                    "password": CryptoJS.SHA512(password).toString(CryptoJS.enc.Hex)
                },
                dataType: "json",
                success: function (data) {
                    if (data.correct == false) {
                        username_el.val("");
                        password_el.val("");
                        tips[0].text("Incorrect username or password.");
                        tips[1].text("Incorrect username or password.");
                    } else if(data.correct == true) {
                        window.location.href = "dashboard.shtml";
                    }
                }
            });
        }
    }

    // Login button click handler
    button_el.click(function () {
        if (verify(document.getElementById("password"))) {
            // Let the user know that we are logging in
            var body_el = $("body"),
                initial = body_el.css("cursor");
            body_el.css("cursor", "wait");
            button_el.attr("disabled", "disabled");
            login(username_el.val(), password_el.val());
            body_el.css("cursor", initial);
            button_el.removeAttr("disabled");
        }
    });

	password_el.keypress(function (e) {
		if (e && e.keyCode == 13) {
			button_el.click();
		}
	});

});
