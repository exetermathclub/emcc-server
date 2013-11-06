import cgi
import sqlite3
import os
import hashlib
import base64
import getpass

if __name__ == "__main__":
    # Initializing
    conn = sqlite3.connect('/home/mathclub/public_html/wsgi-scripts/auth.db')
    cursor = conn.cursor()
    cursor.execute('PRAGMA synchronous=OFF')
    cursor.execute('PRAGMA temp_store=MEMORY')
    username = input('Username: ')
    raw_password = getpass.getpass()
    password = hashlib.sha512(raw_password.encode()).hexdigest()

    # Generate a random salt
    salt = base64.b64encode(os.urandom(128))
    # Hash the password and the salt together
    hashfun = hashlib.sha512()
    hashfun.update(salt)
    hashfun.update(password.encode())
    hashval = hashfun.hexdigest()
    cursor.execute("""
    UPDATE users SET salt = ?, hash = ? WHERE username = ?
    """, (salt, hashval, username))
    cursor.close()
    conn.commit()
