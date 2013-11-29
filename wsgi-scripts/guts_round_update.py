import sqlite3
import json

def application(environ, start_response):
    output = ""
    raw_output = []

    # Open up the teams database
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/scores.db')
    c = conn.cursor()

    c.execute("SELECT team_name, progress, score FROM guts")
    rows = c.fetchall()

    for i in rows:
        raw_output.append({
            "name": i[0],
            "progress": i[1],
            "score": i[2]
        })

    # Close the database connection
    conn.close()

    output = json.dumps({
        "teams": raw_output
    })

    # Actually write everything.
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output))),
                        ('Cache-Control', 'no-cache')]
    start_response(status, response_headers)
    return [output]
