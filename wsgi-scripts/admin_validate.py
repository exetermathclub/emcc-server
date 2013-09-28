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
    # Debugging
    debug = open("/home/mathclub/public_html/wsgi-scripts/debug.txt", "w", encoding='utf-8')

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
    c.execute("SELECT * FROM sesskeys WHERE id=?", (int(form['id'].value),))
    key = auth.dehexify(c.fetchone()[1])

    # If they had the right key, tell them so (otherwise not)
    if auth.decrypt(key, form['message'].value) == "SRP_CLIENT_SUCCESS_MESSAGE":
        output = json.dumps({
            "message": auth.encrypt(key, "SRP_SERVER_SUCCESS_MESSAGE")
        })
    else:
        debug.write("Decrypted:\n%s\nTo:\n%s\n\n" % (form['message'].value,  auth.decrypt(key, form['message'].value)))
        output = json.dumps({
            "error": 13
        })

    debug.close()
    # Close the database connection
    conn.close()

    # Actually write everything.
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output)))]
    start_response(status, response_headers)
    return [output]
