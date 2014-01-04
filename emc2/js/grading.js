$(function () {
    "use strict";
    var password_el = $("#password"),
        round_el = $("#round"),
        teamid_el = $("#teamid"),
        teamname_el = $("#teamname"),
        individ_el = $("#individ"),
        indivname_el = $("#indivname"),
        submit_el = $("#submit"),
        login_el = $("#login"),
        speed_el = $("#speed"),
        speed_inputs_el = $("#speed input"),
        accuracy_el = $("#accuracy"),
        accuracy_inputs_el = $("#accuracy input"),
        team_el = $("#team"),
        team_inputs_el = $("#team input"),
        guts_el = $("#guts"),
        gutsround_el = $("#gutsround"),
        rounds_el = $("div.round"),
        checkboxes_el = $("input[type=\"checkbox\"]"),
        dialog_el = $("#dialog"),
        indivwrapper_el = $("#indiv_wrapper"),
        srp_id,
        srp_key,
        guts_values = [6, 8, 10, 12, 14, 16, 16, 18];

    // Login with the password, copied over from login.js
    function login(password) {
        var rand = generateA();
        $.ajax({
            url: "../wsgi-scripts/grading_auth.py",
            method: "POST",
            data: {
                "A": bigInt2str(rand.A, 16)
            },
            dataType: "json",
            success: function (data) {
                var kdict = generateKey({
                    "uname": "grading",
                    "password": password,
                    "a": rand.a,
                    "A": rand.A
                }, {
                    "B": data.B,
                    "salt": data.s
                });
                $.ajax({
                    url: "../wsgi-scripts/grading_validate.py",
                    method: "GET",
                    data: {
                        "message": JSON.stringify(encrypt(kdict.K, "SRP_CLIENT_SUCCESS_MESSAGE")),
                        "id": data.id
                    },
                    success: function (validation) {
                        if (validation.message !== null && decrypt(kdict.K, JSON.parse(validation.message)) === "SRP_SERVER_SUCCESS_MESSAGE") {
                            localStorage.SRP_SESS_KEY = kdict.K.toString(CryptoJS.enc.Base64);
                            localStorage.SRP_ID = data.id;
                            srp_id = localStorage.SRP_ID;
                            srp_key = CryptoJS.enc.Base64.parse(localStorage.SRP_SESS_KEY);
                            submit_el.removeAttr("disabled");
                            password_el.hide();
                            login_el.hide();
                            $("#loggedin").show();
                        } else {
                            password_el.val("");
                        }
                    }
                });
            }
        });
    }

    // We validate our id and key with the server if they seem to exist
    if (localStorage.SRP_ID && localStorage.SRP_SESS_KEY) {
        $.ajax({
            url: "../wsgi-scripts/grading_validate.py",
            method: "GET",
            data: {
                "message": JSON.stringify(encrypt(CryptoJS.enc.Base64.parse(localStorage.SRP_SESS_KEY), "SRP_CLIENT_SUCCESS_MESSAGE")),
                "id": localStorage.SRP_ID
            },
            success: function (validation) {
                if (validation.message) {
                    if (decrypt(CryptoJS.enc.Base64.parse(localStorage.SRP_SESS_KEY), JSON.parse(validation.message)) === "SRP_SERVER_SUCCESS_MESSAGE") {
                        submit_el.removeAttr("disabled");
                        password_el.hide();
                        login_el.hide();
                        $("#loggedin").show();
                        srp_id = localStorage.SRP_ID;
                        srp_key = CryptoJS.enc.Base64.parse(localStorage.SRP_SESS_KEY);
                    }
                } else {
                    localStorage.removeItem("SRP_ID");
                    localStorage.removeItem("SRP_SESS_KEY");
                    login_el.click(function () {
                        login(password_el.val());
                    });
                }
            }
        });
    } else {
        login_el.click(function () {
            login(password_el.val());
        });
    }

    // As the user types the id, we ask the server for the corresponding team name and the user will verify it.
    teamid_el.keyup(function () {
        $.ajax({
            url: "../wsgi-scripts/team_name.py",
            method: "POST",
            data: {
                id: srp_id,
                team_id: JSON.stringify(encrypt(srp_key, teamid_el.val()))
            },
            success: function (data) {
                teamname_el.text(JSON.parse(decrypt(srp_key, data)));
            },
            error: function () {
                teamname_el.text("");
            }
        });
    });

    // See above.
    individ_el.keyup(function () {
        $.ajax({
            url: "../wsgi-scripts/indiv_name.py",
            method: "POST",
            data: {
                id: srp_id,
                indiv_id: JSON.stringify(encrypt(srp_key, individ_el.val()))
            },
            success: function (data) {
                indivname_el.text(JSON.parse(decrypt(srp_key, data)));
            },
            error: function () {
                indivname_el.text("");
            }
        });
    });

    // We show the checkboxes for the selected round.
    round_el.change(function () {
        switch (round_el.val()) {
        case "speed":
            speed_el.show();
            accuracy_el.hide();
            team_el.hide();
            guts_el.hide();
            indivwrapper_el.show();
            break;
        case "accuracy":
            accuracy_el.show();
            speed_el.hide();
            team_el.hide();
            guts_el.hide();
            indivwrapper_el.show();
            break;
        case "team":
            team_el.show();
            speed_el.hide();
            accuracy_el.hide();
            guts_el.hide();
            indivwrapper_el.hide();
            break;
        default:
            guts_el.show();
            speed_el.hide();
            accuracy_el.hide();
            team_el.hide();
            indivwrapper_el.hide();
        }
    });

    gutsround_el.change(function () {
        rounds_el.hide();
        $("#round" + gutsround_el.val()).show();
    });

    // Serialize all the scores into JSON format.
    function serializeScores() {
        var scores = {}, score = 0, guts_round;
        switch (round_el.val()) {
        case "speed":
            speed_inputs_el.each(function (index) {
                scores[index + 1] = this.checked;
                score += this.checked;
            });
            break;
        case "accuracy":
            accuracy_inputs_el.each(function (index) {
                scores[index + 1] = this.checked;
                score += this.checked;
            });
            break;
        case "team":
            team_inputs_el.each(function (index) {
                scores[index + 1] = this.checked;
                score += this.checked;
            });
            break;
        default:
            guts_round = gutsround_el.val();
            $("#round" + guts_round + " input").each(function (index) {
                scores[3 * parseInt(guts_round) + index - 2] = (this.checked ? guts_values[parseInt(guts_round) - 1] : 0);
                if (this.checked) {
                    score += guts_values[parseInt(guts_round) - 1];
                }
            });
        }
        return {
            scores: scores,
            score: score,
            progress: guts_round
        };
    }

    // Clear the checkboxes and the individual name
    function clearAllInputs() {
        individ_el.val("");
        indivname_el.text("");
        checkboxes_el.prop("checked", false);
        individ_el.focus();
    }

    // We submit our scores.
    submit_el.click(function () {
        var serializedScores = serializeScores(), round = round_el.val(), guts_round = gutsround_el.val();
        if (teamname_el.text() === "") {
            teamname_el.text("Enter a valid team ID.");
            teamid_el.focus();
            return false;
        }
        if (round !== "team" && round !== "guts" && indivname_el.text() === "") {
            indivname_el.text("Enter a valid individual ID.");
            individ_el.focus();
            return false;
        }
        $.ajax({
            url: "../wsgi-scripts/send_grade.py",
            method: "POST",
            data: {
                id: srp_id,
                score_info: JSON.stringify(encrypt(srp_key, JSON.stringify({
                    "team_id": parseInt(teamid_el.val()),
                    "indiv_id": parseInt(individ_el.val()),
                    "round": round,
                    "scores": serializedScores.scores,
                    "score": serializedScores.score,
                    "progress": serializedScores.progress
                })))
            },
            success: function (data) {
                if (data.success) {
                    dialog_el.show();
                    setTimeout(function () {
                        dialog_el.hide();
                    }, 2000);
                    clearAllInputs();
                }
            }
        });
    });
});
