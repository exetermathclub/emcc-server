import cgi
import sqlite3
import os
import json
import hashlib
import base64
import time

def application(environ, start_response):
    # Initializing
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/auth.db')
    cursor = conn.cursor()
    cursor.execute('PRAGMA synchronous=OFF')
    cursor.execute('PRAGMA temp_store=MEMORY')
    form = cgi.FieldStorage(
        fp=environ['wsgi.input'],
        environ=environ,
        keep_blank_values=True
    )
    output = json.dumps({
        "success": True
    })

    username = form['username'].value
    password = form['password'].value
    key = form['key'].value

    # Verify that the user has the key
    cursor.execute('SELECT key, key_timestamp FROM users WHERE username = ?', (username,))
    (server_key, key_timestamp) = cursor.fetchone()
    if server_key == key:
        if time.time() - key_timestamp < 86400:
            # Generate a random salt
            salt = base64.b64encode(os.urandom(36))
            # Hash the password and the salt together
            hashfun = hashlib.sha512()
            hashfun.update(salt)
            hashfun.update(password.encode())
            hashval = hashfun.hexdigest()
            cursor.execute("UPDATE users SET salt = ?, hash = ?, key = NULL WHERE username = ?", (salt, hashval, username))
            cursor.close()
            conn.commit()
        else:
            output = json.dumps({
                "success": False,
                "error": "Outdated key"
            })
    else:
        output = json.dumps({
            "success": False,
            "error": "Invalid key"
        })

    status = '200 OK'
    response_headers = [('Content-type', 'text/plain'),
                        ('Content-Length', str(len(output)))]
    start_response(status, response_headers)

    return [output]
