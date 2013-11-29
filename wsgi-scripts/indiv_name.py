import cgi
import sqlite3
import json
import imp
auth = imp.load_source("auth", "/home/mathclub/public_html/wsgi-scripts/auth.py")

def application(environ, start_response):
    # Open the srp databse connection
    conn = auth.initDB("/home/mathclub/public_html/wsgi-scripts/auth_srp.db")
    c = conn.cursor()
    output = ""

    form = cgi.FieldStorage(
        fp=environ['wsgi.input'],
        environ=environ,
        keep_blank_values=True
    )
    
    # Get this user's session key
    c.execute("SELECT sesskey FROM grading_sesskeys WHERE id=?", (int(form['id'].value),))
    key = auth.dehexify(c.fetchone()[0])

    individ = auth.decrypt(key, form['indiv_id'].value)
    
    # Close the database connection
    conn.close()

    # Open up the teams database
    if len(individ) > 1:
        conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/scores.db')
        c = conn.cursor()
        
        c.execute("SELECT indiv_name FROM speed WHERE indiv_id = ?", (individ,))
        row = c.fetchone()

        if row is not None:
            # Get the individual's name
            indiv_name = row[0]
            
            # Write it all
            output = auth.encrypt(key, json.dumps(indiv_name))

    # Close the database connection
    conn.close()

    # Actually write everything.
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output))),
                        ('Cache-Control', 'no-cache')]
    start_response(status, response_headers)
    return [output]
