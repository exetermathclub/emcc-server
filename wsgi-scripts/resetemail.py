import json
import sqlite3
import os
import base64
import smtplib
import sys
import traceback
from email.mime.text import MIMEText
import cgi

def application(environ, start_response):
    # Open our database connection
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/auth.db')
    cursor = conn.cursor()
    cursor.execute('PRAGMA temp_store=MEMORY')

    # Output defaults to success
    output = json.dumps({
        "success": True
    })

    # Get the username
    username = cgi.parse_qs(environ.get('QUERY_STRING', ''))['username'][0]

    # Add the reset key into the database
    key = base64.b64encode(os.urandom(32)).decode()
    cursor.execute('UPDATE users SET key = ? WHERE username = ?', (key, username))
    
    # Get the email
    cursor.execute('SELECT email FROM users WHERE username = ?', [username])
    email = cursor.fetchone()[0]
    
    # We're done with our database at this point
    cursor.close()
    conn.commit()
    
    link_url = "https://csserver.exeter.edu/~mathclub/emc2/reset.shtml?username=" + cgi.escape(username) + "&key=" + cgi.escape(key)

    # Format the email that we're about to send
    raw_msg = """
        Use the following link before midnight to reset your password:<br/>
        <a href="%s">%s</a><br/><br/>

        Thanks,<br/>
        Exeter Math Club
    """ % (link_url, link_url)

    msg = MIMEText(raw_msg, 'html')
    msg['Subject'] = 'EMCC Password Reset'
    msg['From'] = 'emcc2014@gmail.com'
    msg['To'] = email

    # Log in to gmail's smtp with the emcc2014 credentials
    server = smtplib.SMTP('smtp.gmail.com:587')
    server.starttls()
    server.login('emcc2014', open('/home/mathclub/public_html/wsgi-scripts/PASSWORD', 'r').read()[:-1])
    
    # Send the email
    try:
        server.sendmail('donotreply@emcc.exeter.edu', email, msg.as_string())
    except smtplib.SMTPRecipientsRefused:
        # If we failed because the target email is misformatted, say so
        output = json.dumps({
            "success": False,
            "error": "misformatted email"
        })
    
    # Log out of the smtp server
    server.quit()

    # Put the response to the client.
    status = '200 OK'
    response_headers = [('Content-type', 'text/plain'),
                        ('Content-Length', str(len(output))),
                        ('Max-Age', '0')]
    start_response(status, response_headers)
    return [output]
