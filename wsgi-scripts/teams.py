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

    # Determine whether they match
    if client_cookie == server_cookie:
        return True
    else:
        return False

def register(uname, form):
    # Connect to the teams database
    (conn, cursor) = setup_cursor()

    # Create the team
    cursor.execute('''
    INSERT INTO teams (username, name, members, paid, participation) VALUES (?, ?, ?, ?, ?)
    ''', (uname, form['name'].value, form['members'].value, False, form['participation'].value == 'true'))

    # Commit our changes and close the database
    conn.commit()
    conn.close()

    # Tell the client the team id
    return json.dumps({
        "id": cursor.lastrowid
    })

def edit(form):
    # Connect to the teams database
    (conn, cursor) = setup_cursor()

    # Update the team
    cursor.execute('UPDATE teams SET name = ?, members = ?, participation = ? WHERE id = ?', (form['name'].value, form['members'].value, form['participation'].value == 'true', form['id'].value))

    # Commit and close the teams database
    conn.commit()
    conn.close()

    # Tell the client whether the edit was successful
    return json.dumps({
        "success": cursor.rowcount == 1
    })

def print_list(uname):
    # Connect to the teams database
    (conn, cursor) = setup_cursor()

    # Get all the teams in this database
    cursor.execute('SELECT * FROM teams WHERE username = ?', [uname])
    rows = cursor.fetchall()

    # Close the teams database
    conn.close()
    # Format the teams
    teams = []
    for row in rows:
        teams.append({
            "id": row[0],
            "name": row[2],
            "members": json.loads(row[3]),
            "paid": row[4],
            "participation": row[5]
        })
    # JSON serialize them and return
    return json.dumps({
        "teams": teams
    })

def delete(form):
    # Connect to the teams database
    (conn, cursor) = setup_cursor()

    # Delete the requested team
    cursor.execute('DELETE FROM teams WHERE id = ?', [form['id'].value])

    # Commit and close the teams database
    conn.commit()
    conn.close()

    # Return whether the delete was successful
    return json.dumps({
        "success": cursor.rowcount == 1
    })

def application(environ, start_response):
    
    output = ''

    if check(environ['HTTP_COOKIE']):
        # Parse the CGI args and the cookie
        uname = username(environ['HTTP_COOKIE'])
        form = cgi.FieldStorage(
            fp=environ['wsgi.input'],
            environ=environ,
            keep_blank_values=True
        )

        # Get the requested action
        purpose = form['purpose'].value

        # Perform this action
        if purpose == 'register':
            output = register(uname, form)
        elif purpose == 'edit':
            output = edit(form)
        elif purpose == 'delete':
            output = delete(form)
        else:
            output = print_list(uname)

    # Output our headers
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output))),
                        ('Cache-Control', 'max-age=0')]
    start_response(status, response_headers)

    # Write everything to the client
    return [output]
