import sqlite3
import json

if __name__ == "__main__":
    conn = sqlite3.connect("teams.db")
    c = conn.cursor()
    c.execute("SELECT id, name, members FROM teams")
    a = c.fetchall()
    conn.close()
    conn = sqlite3.connect("scores.db")
    c = conn.cursor()
    c.execute("DELETE FROM speed")
    c.execute("DELETE FROM accuracy")
    c.execute("DELETE FROM team")
    c.execute("DELETE FROM guts")
    for i in a:
        members = json.loads(i[2])
        for j in range(4):
            c.execute("INSERT INTO speed (team_id, indiv_id, team_name, indiv_name) VALUES (?, ?, ?, ?)", (i[0], 10 * i[0] + j + 1, i[1], members[j]))
            c.execute("INSERT INTO accuracy (team_id, indiv_id, team_name, indiv_name) VALUES (?, ?, ?, ?)", (i[0], 10 * i[0] + j + 1, i[1], members[j]))
        c.execute("INSERT INTO team (team_id, team_name) VALUES (?, ?)", (i[0],i[1]))
        c.execute("INSERT INTO guts (team_id, team_name, progress, score, answers) VALUES (?, ?, 0, 0, ?)", (i[0], i[1], '{"24": 0, "20": 0, "21": 0, "22": 0, "23": 0, "1": 0, "3": 0, "2": 0, "5": 0, "4": 0, "7": 0, "6": 0, "9": 0, "8": 0, "11": 0, "10": 0, "13": 0, "12": 0, "15": 0, "14": 0, "17": 0, "16": 0, "19": 0, "18": 0}'))
    conn.commit()
    conn.close()
