$(function () {
    "use strict";
    var teams,
        current_viewed_team = -1,
        team_list           = $("#team_list"),
        team_edit_pane      = $("#team_edit_pane"),
        team_member_inputs  = $(".team_member"),
        team_paid           = $("#paid"),
        team_participation  = $("#participation"),
        team_name           = $("#team_name"),
        team_new            = $("#new"),
        team_delete         = $("#delete");

    // Get the list of teams belonging to this user
    function reloadTeams() {
        $.ajax({
            url: "../wsgi-scripts/teams.py",
            method: "GET",
            data: {
                purpose: "list"
            },
            dataType: "json",
            success: function (data) {
                var i;

                team_list.html("");
                teams = data.teams;
                function nameClick() {
                    var team = teams[i];
                    return function () {
                        // Toggle the edit pane
                        if (team_edit_pane.css("display") === "none") {
                            team_edit_pane.hide();
                            team_edit_pane.show();
                            $("#new").hide();
                        }
                        current_viewed_team = team.id;

                        // Set the inputs to be the information for this team
                        team_name.val(team.name);
                        team_member_inputs.each(function (k) {
                            if (team.members[k]) {
                                this.value = team.members[k];
                            } else {
                                this.value = "";
                            }
                        });
                        if (team.paid) {
                            team_paid.text("Paid");
                        } else {
                            team_paid.text("Unpaid");
                        }
                        if (team.participation) {
                            team_participation.val("On-Site");
                        } else {
                            team_participation.val("Online");
                        }
                    };
                }
                for (i = 0; i < teams.length; i += 1) {
                    team_list.append($("<div>").addClass("team").text(teams[i].name).click(nameClick()));
                }
            },
            error: function () {
                window.location.href = "login.shtml";
            }
        });
    }

    reloadTeams();

    team_new.click(function () {
        team_name.val("");
        team_member_inputs.val("");
        team_delete.hide();
        team_new.hide();
        team_edit_pane.show();
        current_viewed_team = -1;
    });

    $("#cancel").click(function () {
        team_new.show();
        team_edit_pane.hide();
    });

    function getTeamMembers() {
        var team_members = [];
        team_member_inputs.each(function () {
            var team_member = this.value;
            team_members.push(team_member);
        });
        return team_members;
    }

    $("#save").click(function () {
        if (current_viewed_team === -1) {
            //Register a new team with this info
            $.ajax({
                url: "../wsgi-scripts/teams.py",
                method: "POST",
                data: {
                    "purpose": "register",
                    "name": team_name.val(),
                    "members": JSON.stringify(getTeamMembers()),
                    "participation": (team_participation.val() === "On-Site").toString()
                },
                success: function () {
                    team_edit_pane.hide();
                    reloadTeams();
                    team_new.show();
                    $("#delete").show();
                }
            });
        } else {
            $.ajax({
                url: "../wsgi-scripts/teams.py",
                method: "POST",
                data: {
                    "purpose": "edit",
                    "id": current_viewed_team,
                    "name": team_name.val(),
                    "members": JSON.stringify(getTeamMembers()),
                    "participation": team_participation.val() === "On-Site"
                },
                success: function () {
                    team_edit_pane.hide();
                    reloadTeams();
                    team_new.show();
                }
            });
        }
    });

    $("#delete").click(function () {
        if (!confirm("Are you sure you want to delete this team?")) {
            return;
        }
        $.ajax({
            url: "../wsgi-scripts/teams.py",
            method: "POST",
            data: {
                "purpose": "delete",
                "id": current_viewed_team
            },
            success: function () {
                team_edit_pane.hide();
                reloadTeams();
                team_new.show();
            }
        });
    });

    $.ajax({
        url: "../wsgi-scripts/checkemail.py",
        method: "POST",
        success: function (data) {
            if (data === "False") {
                $(".dialog").show();
                $(".cover").show();
                $("#dialog_submit").click(function () {
                    if ($("#dialog_input").val().indexOf("@") > -1) {
                        $.ajax({
                            url: "../wsgi-scripts/modify.py",
                            method: "POST",
                            data: {
                                "columns": JSON.stringify([2]),
                                "values": JSON.stringify([$("#dialog_input").val()])
                            },
                            success: function () {
                                $(".dialog").hide();
                                $(".cover").hide();
                            }
                        });
                    } else {
                        $("#dialog_tip").text("Please enter a valid email.");
                    }
                });
            }
        }
    });
});
