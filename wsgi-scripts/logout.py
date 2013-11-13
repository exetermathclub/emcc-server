from http import cookies
import sqlite3
import cgi
import json

def setup_cursor():
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/teams.db')
    cursor = conn.cursor()
    cursor.execute('PRAGMA synchronous=OFF')
    cursor.execute('PRAGMA temp_store=memory')
    return (conn, cursor)

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
    if check(environ['HTTP_COOKIE']):
        uname = username(environ["HTTP_COOKIE"])
        conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/auth.db')
        cursor = conn.cursor()
        cursor.execute("UPDATE users SET cookie='' WHERE username=?", (uname,))
        conn.commit()
        conn.close()

    output = "{\"success\":true}"
    # Output our headers
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output))),
                        ('Set-Cookie', 'EMCC=null; path=/; expires=Thu, Jan 01 1970 00:00:00 UTC;'),
                        ('Cache-Control', 'no-cache')]
    start_response(status, response_headers)

    # Write everything to the client
    return [output]
