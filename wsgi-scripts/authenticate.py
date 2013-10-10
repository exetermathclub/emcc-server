import cgi
import sqlite3
import os
import hashlib
import json
import base64
from http import cookies

def application(environ, start_response):
    output = "{\"success\":\"\"}"
    correct = False
    # Initializing
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/auth.db')
    cursor = conn.cursor()
    form = cgi.FieldStorage(
        fp=environ['wsgi.input'],
        environ=environ,
        keep_blank_values=True
    )
    username = form['username'].value
    password = form['password'].value
    cursor.execute("SELECT salt, hash FROM users WHERE username = ?", [username])
    ret = cursor.fetchone()
    
    output = None

    if ret is not None:
        salt = ret[0]
        dbhash = ret[1]
        hashfun = hashlib.sha512()
        hashfun.update(salt)
        hashfun.update(password.encode())
        hashval = hashfun.hexdigest()
        cookie = cookies.SimpleCookie()
        cookie_text = username + ":" + base64.b64encode(os.urandom(128)).decode()
        cookie['EMCC'] = cookie_text
        cookie['EMCC']['max-age'] = str(24 * 60 * 60) # Cookie expires in one day
        cookie['EMCC']['path'] = '/'
        cursor.execute("UPDATE users SET cookie = ? WHERE username = ?", (cookie_text, username))
        output = json.dumps({"correct":hashval==dbhash})
        correct = True
    else:
        output = json.dumps({
            "correct": False
        })
    conn.commit()
    conn.close()

    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output)))]
    if correct:
        response_headers.append(('Set-Cookie', cookie.output().replace('Set-Cookie: ', '', 1)))
    start_response(status, response_headers)

    return [output]
