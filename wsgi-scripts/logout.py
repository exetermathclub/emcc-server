#!/usr/bin/env python
import sqlite3
import cookie
import os
from http import cookies

# Initializing
print("Content-type: text/plain")
if(cookie.check(os.environ['HTTP_COOKIE'])):
	conn = sqlite3.connect('auth.db')
	c = conn.cursor()

	server_cookie = cookies.SimpleCookie()
	server_cookie['EMCC'] = ''
	server_cookie['EMCC']['max-age'] = '0'

	username = cookie.username(os.environ['HTTP_COOKIE'])
	c.execute('UPDATE users SET cookie = ? WHERE username = ?', ('', username))

	print(server_cookie)
print()
