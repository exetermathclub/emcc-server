$(function () {
    "use strict";
    var teams,
        display_names       = [],
        ids                 = [],
        current_viewed_team = -1,
        team_list           = $("#team_list"),
        team_edit_pane      = $("#team_edit_pane"),
        team_member_inputs  = $(".team_member"),
        team_paid           = $("#paid"),
        team_participation  = $("#participation"),
        team_name           = $("#team_name"),
        team_new            = $("#new"),
        team_delete         = $("#delete");

    function move_editpane(id) {
        /*
         * Move the edit pane to the team at position (id)
         */
        
        //We save this for closure purposes
        var team = teams[id];

        if (id === current_viewed_team) {
            //If we're already here, toggle the edit pane instead
            if (team_edit_pane.css("display") !== "none") team_edit_pane.hide();
            else team_edit_pane.show();
        }
        else {
            //Otherwise, move the edit pane, first setting the global current_viewed_team state
            current_viewed_team = id;
            
            //Remove the existing edit pane, and show the "new team" button if it is invisible
            team_new.show();
            team_edit_pane.detach();

            //Update the edit pane fields
            team_name.val(team.name);
            team_member_inputs.each(function (index) {
                this.value = team.members[index];
            });
            team_paid.text(team.paid ? "Paid" : "Unpaid");
            team_participation.val(team.participation ? "On-Site" : "Online");

            //Reappend the edit pane where we want it, then make it visible
            display_names[id].after(team_edit_pane);
            team_edit_pane.show();
       }
    }

    function getID(obj) {
        /*
         * Get the ordinal id associated with DOM element obj
         */

        var i;
        for (i = 0; i < display_names.length; i += 1) {
            //Brute-force
            if (obj === display_names[i][0]) {
                return i;
            }
        }
    }

    function nameClick(event) {
        move_editpane(getID(event.target));
    }

    /*
     * Get the teams belonging to this user
     */
    $.ajax({
        url: "../wsgi-scripts/teams.py",
        method: "GET",
        data: {
            purpose: "list"
        },
        success: function (data) {
            var i, obj;
            teams = data.teams;

            // Generate the display names of the teams
            for (i = 0; i < teams.length; i += 1) {
                ids[i] = teams[i].id;
                obj = $("<div>").addClass("team").text(teams[i].name).click(nameClick);
                display_names.push(obj);
                team_list.append(obj);
            }
        }
    });

    function getData(purpose) {
        var name = team_name.val(),
            members = [],
            participation = "";
        team_member_inputs.each(function () {
            members.push(this.value);
        });
        participation = (team_participation.val() === "On-Site").toString();
        if (purpose === "edit") {
            return {
                "purpose": purpose,
                "id": ids[current_viewed_team],
                "name": name,
                "members": JSON.stringify(members),
                "participation": participation
            };
        }
        return {
            "purpose": purpose,
            "name": name,
            "members": JSON.stringify(members),
            "participation": participation
        };
    }

    function register() {
        var ret;
        $.ajax({
            url: "../wsgi-scripts/teams.py",
            method: "POST",
            data: getData("register"),
            success: function (data) {
                ret = data.id;
            }
        });
        return ret;
    }

    function edit() {
        $.ajax({
            url: "../wsgi-scripts/teams.py",
            method: "POST",
            data: getData("edit")
        });
    }

    function delete_team(team_id) {
        $.ajax({
            url: "../wsgi-scripts/teams.py",
            method: "POST",
            data: {
                purpose: "delete",
                id: ids[team_id]
            }
        });
    }


    $("#save").click(function () {
        var members = [], id, len = ids.length, obj;
        team_member_inputs.each(function () {
            members.push(this.value);
        });
        if (current_viewed_team === -1) {
            id = register();
            ids[len] = id;
            teams[len] = {
                id : id,
                name : team_name.val(),
                members : members,
                participation : team_participation.val() === "On-Site",
                paid: false
            };
            obj = $("<div>").addClass("team").text(teams[len].name).click(nameClick);
            display_names.push(obj);
            team_list.append(obj);
        } else {
            edit();
            teams[current_viewed_team].name = team_name.val();
            teams[current_viewed_team].members = members;
            teams[current_viewed_team].participation = team_participation.val() === "On-Site";
            display_names[current_viewed_team].text(team_name.val());
        }
        team_edit_pane.hide();
    });

    team_delete.click(function () {
        delete_team(current_viewed_team);
        delete teams[current_viewed_team];
        display_names[current_viewed_team].remove();
        display_names.splice(current_viewed_team, current_viewed_team);
        team_edit_pane.hide();
    });

    $("#cancel").click(function () {
        team_new.show();
        team_edit_pane.hide();
    });

    team_new.click(function () {
        current_viewed_team = -1;
        team_delete.hide();
        team_name.val("");
        team_member_inputs.each(function () {
            this.value = "";
        });
        team_paid.text("Unpaid");
        team_participation.val("On-Site");
        team_new.after(team_edit_pane);
        team_new.hide();
        team_edit_pane.show();
    });

    $.ajax({
        url: "../wsgi-scripts/checkemail.py",
        method: "GET",
        dataType: "json",
        success: function (data) {
            if (!data.success && data.error == 1) {
                $(".dialog").show();
                $(".cover").show();
                $("#dialog_submit").click(function () {
                    if ($("#dialog_input").val().indexOf("@") > -1) {
                        $.ajax({
                            url: "../wsgi-scripts/modify.py",
                            method: "POST",
                            data: {
                                "columns": "[2]",
                                "values": "[" + $("#dialog_input").val() + "]"
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
            } else if (data.error === 0) {
                window.location.href= "login.shtml";
            }
        }
    });
});
