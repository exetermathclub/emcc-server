import json
import time

def application(environ, start_response):
    time_end = 0
    with open("/home/mathclub/public_html/wsgi-scripts/time_end.json") as time_file:
        time_end = json.load(time_file)['time']
    
    output = json.dumps({
        'time_end': time_end,
        'time_now': time.time(),
        'time': int(time_end - time.time())
    })

    # Actually write everything.
    status = '200 OK'
    response_headers = [('Content-type', 'application/json'),
                        ('Content-Length', str(len(output))),
                        ('Cache-Control', 'no-cache')]
    start_response(status, response_headers)
    return [output]
