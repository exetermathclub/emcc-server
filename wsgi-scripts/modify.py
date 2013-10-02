import cgi
import json
import sqlite3
import os
import hashlib
import base64
from http import cookies

def username(raw_cookie):
    text = cookies.SimpleCookie(raw_cookie)['EMCC'].value
    return text[0:text.index(':')]

def check(raw_cookie):
    # Parse the cookie
    container = cookies.SimpleCookie(raw_cookie)

    # If they don't _have_ the cookie, it's clearly not right
    if 'EMCC' not in container:
        return False

    # Otherwise, get the cookie
    client_cookie = container['EMCC'].value
    uname = username(raw_cookie)

    # Connect to the auth database
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/auth.db')
    c = conn.cursor()

    # Get the true cookie
    c.execute("SELECT cookie FROM users WHERE username = ?", [uname])
    server_cookie = c.fetchone()[0]

    conn.close()

    # Determine whether they match
    if client_cookie == server_cookie:
        return True
    else:
        return False

def application(environ, start_response):
    # Initializing
    output = ""
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/auth.db')
    cursor = conn.cursor()
    cursor.execute('PRAGMA synchronous=OFF')
    cursor.execute('PRAGMA temp_store=MEMORY')
    form = cgi.FieldStorage(
        fp=environ['wsgi.input'],
        environ=environ,
        keep_blank_values=True
    )
    if check(environ['HTTP_COOKIE']):
        uname     = username(environ['HTTP_COOKIE'])
        columns   = json.loads(form['columns'].value)
        values    = json.loads(form['values'].value)

        # Accessing the database
        for column in columns:
            for value in values:
                cursor.execute("UPDATE users SET %s=? WHERE username=?" % (["username", "realname", "email", "orgname", "address", "salt", "hash"][column],), (value, uname))
        conn.commit()
        conn.close()
    status = '200 OK'
    response_headers = [('Content-type', 'text/plain'),
                        ('Content-Length', str(len(output)))]
    start_response(status, response_headers)

    return [output]
