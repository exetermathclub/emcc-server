#!/usr/bin/env python
import json
import time

with open("time_end.json", "w") as time_start:
    time_start.write(json.dumps({
        "time": time.time() + 4200
    }))
