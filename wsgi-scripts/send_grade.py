import cgi
import sqlite3
import json
import traceback
import imp
auth = imp.load_source("auth", "/home/mathclub/public_html/wsgi-scripts/auth.py")

def application(environ, start_response):
    # Open the srp databse connection
    conn = auth.initDB("/home/mathclub/public_html/wsgi-scripts/auth_srp.db")
    c = conn.cursor()
    c.execute("PRAGMA temp_store=MEMORY")
    c.execute("PRAGMA synchronous=OFF")
    output = ""

    form = cgi.FieldStorage(
        fp=environ['wsgi.input'],
        environ=environ,
        keep_blank_values=True
    )

    # Get this user's session key
    c.execute("SELECT sesskey FROM grading_sesskeys WHERE id = ?", (int(form['id'].value),))
    key = auth.dehexify(c.fetchone()[0])
    
    score_info = json.loads(auth.decrypt(key, form['score_info'].value));

    # Close the database connection
    conn.close()
    
    # Open up the teams database
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/scores.db')
    c = conn.cursor()

    if score_info['round'] == 'speed' or score_info['round'] == 'accuracy':
        c.execute("UPDATE %s SET answers = ?, score = ? WHERE team_id = ? AND indiv_id = ?" % score_info['round'], (json.dumps(score_info['scores']), score_info['score'], score_info['team_id'], score_info['indiv_id']))
    elif score_info['round'] == 'team':
        c.execute("UPDATE team SET answers = ?, score = ? WHERE team_id = ?", (json.dumps(score_info['scores']), score_info['score'], score_info['team_id']))
    elif score_info['round'] == 'guts':
        c.execute("SELECT progress, answers, score FROM guts WHERE team_id = ?", (score_info['team_id'],))
        row = c.fetchone()
        progresses = [18, 42, 72, 108, 150, 198, 246, 300]
        progress = progresses[int(score_info['progress']) - 1]
        if progress > row[0]:
            score = row[2] + score_info['score']
        else:
            score = row[2]
        scores = json.loads(row[1]).copy()
        scores.update(score_info['scores'])
        c.execute("UPDATE guts SET answers = ?, progress = ?, score = ? WHERE team_id = ?", (json.dumps(scores), progress, score, score_info['team_id']))
    conn.commit()
    
    # Close the database connection
    conn.close()

    output += json.dumps({
        "success": True
    })

    # Actually write everything.
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output))),
                        ('Cache-Control', 'no-cache')]
    start_response(status, response_headers)
    return [output]
