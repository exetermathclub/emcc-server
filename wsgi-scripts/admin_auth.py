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
    # Open up the authentication database
    conn = auth.initDB("/home/mathclub/public_html/wsgi-scripts/auth_srp.db")
    c = conn.cursor()
    output = ""
    
    # Make sure that we have a working sesskeys table
    c.execute("CREATE TABLE IF NOT EXISTS sesskeys (id INTEGER PRIMARY KEY ASC, sesskey TEXT)")
    
    # Retrieve the data the client sent us
    form = cgi.FieldStorage(
        fp = environ['wsgi.input'],
        environ = environ,
        keep_blank_values = True
    )
    
    kdict = auth.generateKey(conn, "admin", int(form['A'].value, 16))
    
    # Remember this session key
    c.execute("INSERT INTO sesskeys (sesskey) VALUES (?)", (auth.hexify(kdict["K"]),))
    conn.commit()
    conn.close()
    
    # Write what the client needs to know to generate the same key
    output = json.dumps({
        "s": kdict["s"],
        "B": kdict["B"],
        "id": c.lastrowid
    })

    # Actually write everything.
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output)))]
    start_response(status, response_headers)
    return [output]
