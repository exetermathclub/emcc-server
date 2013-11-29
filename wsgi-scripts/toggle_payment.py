import cgi
import sqlite3
import os
import hashlib
import json
import base64
import imp
auth = imp.load_source("auth", "/home/mathclub/public_html/wsgi-scripts/auth.py")
from http import cookies

def application(environ, start_response):
    # Open the srp databse connection
    conn = auth.initDB("/home/mathclub/public_html/wsgi-scripts/auth_srp.db")
    c = conn.cursor()

    form = cgi.FieldStorage(
        fp=environ['wsgi.input'],
        environ=environ,
        keep_blank_values=True
    )
    
    # Get this user's session key
    c.execute("SELECT sesskey FROM sesskeys WHERE id=?", (int(form['id'].value),))
    key = auth.dehexify(c.fetchone()[0])
    
    # Close the database connection
    conn.close()
    
    # Open up the teams database
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/teams.db')
    c = conn.cursor()
    
    rowid = int(auth.decrypt(key, form['teamid'].value))

    success = True
    try:
        c.execute("UPDATE teams SET paid=? WHERE id=?", (int(form['paid'].value), rowid))
    except sqlite3.OperationalError:
        success = False
    
    output = json.dumps({
        "success": success
    })

    # Close the database connection
    conn.commit()
    conn.close()

    # Actually write everything.
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output)))]
    start_response(status, response_headers)
    return [output]
