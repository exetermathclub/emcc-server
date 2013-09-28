import cgi
import sqlite3
import os
import hashlib
import base64

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
    username = form['username'].value
    realname = form['realname'].value
    password = form['password'].value
    orgname  = form['orgname'].value
    address  = form['address'].value

    # Accessing the database to see if username is used
    cursor.execute("""
    SELECT * FROM users WHERE username = ?
    """, [username])
    result = cursor.fetchall()
    output = ""
    if len(result) > 0:
        output = "false"
    else:
        # Generate a random salt
        salt = base64.b64encode(os.urandom(128))
        # Hash the password and the salt together
        hashfun = hashlib.sha512()
        hashfun.update(salt)
        hashfun.update(password.encode())
        hashval = hashfun.hexdigest()
        cursor.execute("""
        INSERT INTO users (username, realname, salt, hash, orgname, address) VALUES (?, ?, ?, ?, ?, ?)
        """, (username, realname, salt, hashval, orgname, address))
        cursor.close()
        conn.commit()
        output = "true"

    status = '200 OK'
    response_headers = [('Content-type', 'text/plain'),
                        ('Content-Length', str(len(output)))]
    start_response(status, response_headers)

    return [output]
