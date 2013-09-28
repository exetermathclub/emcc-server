$(function() {
    //Retrieve the locally stored session key and id
    var srp_string = localStorage.getItem("SRP_SESS_KEY"),
        srp_id = localStorage.getItem("SRP_ID"),
        srp_key;
    
    console.log(srp_string, srp_id);

    //If we haven't logged in correctly, tell the user to do so
    if (srp_string == null || srp_id == null) location.href = "login.shtml";
    
    //Parse the locally stored srp key
    srp_key = CryptoJS.enc.Base64.parse(srp_string);
    
    function team_format(row) {
        return $("<tr>")
            .append($("<td class='team_id'>").text(row[0]))
            .append($("<td class='team_coach'>").text(row[1]))
            .append($("<td class='team_name'>").text(row[2]))
            .append($("<td class='team_members'>").text(JSON.parse(row[3]).join(", ")))
            .append($("<td class='team_participation'>").text(row[5] ? "On-Site" : "Online"))
            .append($("<td class='team_payment'>").append((row[4] ? $("<input type='checkbox'/>").attr("checked", "") : $("<input type='checkbox'/>"))
                .addClass("payment_checkbox")
                .click(function() {
                    $.ajax({
                        "url": "../wsgi-scripts/toggle_payment.py",
                        "data": {
                            "teamid": JSON.stringify(encrypt(srp_key, row[0].toString())),
                            "id": srp_id,
                            "paid": this.checked ? 1 : 0
                        }
                    });
                })
            ))
            .append($("<td class='team_deletion'>").append($("<button class='field'>").text("Delete")
                .click(function() {
                    if (confirm("Are you sure you want to delete " + row[2] + "?")) {
                        $.ajax({
                            "url": "../wsgi-scripts/delete_team.py",
                            "data": {
                                "teamid": JSON.stringify(encrypt(srp_key, row[0].toString())),
                                "id": srp_id,
                            }
                        });
                    }
                })
            ));
    }

    //Request all the teams
    $.ajax({
        "url": "../wsgi-scripts/admin_teams.py",
        "method": "GET",
        "data": {
            "id": srp_id
        },
        "dataType": "json",
        "success": function (data) {
            var decoded = JSON.parse(decrypt(srp_key, data)),
                team_list_el = $("#team_table");
            console.log(decoded);
            for (var i = 0; i < decoded.length; i += 1) {
                team_list_el.append(team_format(decoded[i]));
            }
        }
    });
});
