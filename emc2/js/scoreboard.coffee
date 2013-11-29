MAX_SCORE = 400
remaining_time = 0
search_found = false
teams = []
highlighted = $("")
refresh_search = (x) -> x
doc_body = null
position = 0

refreshTeams = (callback) ->
  $.ajax {
    url: "../wsgi-scripts/guts_round_update.py"
    dataType: "json"
    success: (data) ->
      data.teams.sort((a, b) -> b.score-a.score)
      callback data.teams, -> refreshTeams callback
  }

syncTime = (callback) ->
  $.ajax {
    url: "../wsgi-scripts/guts_time_sync.py"
    dataType: "json"
    success: (data) ->
      callback data
      setTimeout (-> syncTime callback), 10000
  }

zeroPad = (n, digits) ->
  str = n.toString()
  if str.length < digits
    zeroes = ("0" for _ in [1..(digits - str.length)]).join ""
    return zeroes + str
  else
    return str

formatTime = (time) ->
  hours = Math.floor(time / 3600)
  time -= hours * 3600
  minutes = Math.floor(time / 60)
  time -= minutes * 60
  return zeroPad(hours, 2) + ":" + zeroPad(minutes, 2) + ":" + zeroPad(time, 2)

formatTeam = (team) ->
  $("<div>").addClass("team")
  .append($("<div>").addClass("team_name").text(team.name))
  .append($("<div>").addClass("team_score").text(team.score + " (" + team.progress + ")"))
  .append($("<div>").addClass("team_bar")
  .append($("<div>").addClass("team_scorebar").width((team.score / MAX_SCORE * 100) + "%"))
  .append($("<div>").addClass("team_progressbar").width(((team.progress - team.score)/MAX_SCORE * 100) + "%")))

searchTeamPrefix = (search_prefix) ->
  search_prefix = search_prefix.toLowerCase()
  search_length = search_prefix.length
  for team in teams
    if team.name[0..(search_length-1)].toLowerCase() == search_prefix
      search_found = true
      highlighted = team.el
      doc_body.scrollTop team.el.addClass("highlighted").offset().top
      return -> searchTeamPrefix search_prefix
  return (x) -> x

searchTeamExact = (search) ->
  search = search.toLowerCase()
  for team in teams
    if team.name.toLowerCase() == search
      search_found = true
      highlighted = team.el
      doc_body.scrollTop team.el.addClass("highlighted").offset().top
      return -> searchTeamExact search
  return (x) -> x

window.onload = ->
  # Start syncing remaining time with the server's
  syncTime (data) ->
    remaining_time = data.time
  
  # Start updating the timer element
  timer = $ "div#timer"
  time_interval = setInterval (->
    timer.text formatTime remaining_time
    remaining_time -= 1), 1000
  
  body = $ "div#body"
  doc_body = $(document.body)
  animation_limit = 0
  animation_interval = 0
  tick = (cont, time) ->
    if not search_found
      position += animation_interval
      doc_body.scrollTop position
    if position > animation_limit or (new Date()).getTime() - time > 10000
      position = 0
      setTimeout cont, 500
    else
      setTimeout (-> tick cont, time), 1

  $(window).scroll ->
    if Math.abs(doc_body.scrollTop() - position) > 2
      position = doc_body.scrollTop()

  # Start refreshing all the team scores
  refreshTeams (data, cont) ->
    teams.length = 0
    body.html ""
    for team in data
      element = formatTeam team
      body.append element
      teams.push $.extend({el: element}, team)
    refresh_search()
    animation_limit = (body.height() - $(window).height())
    animation_interval = animation_limit / 5000
    tick cont, (new Date()).getTime()
  
  $("#search").keyup (e) ->
    highlighted.removeClass "highlighted"
    highlighted = $("")
    if this.value.length == 0
      search_found = false
      refresh_search = (x) -> x
      return
    if e.keyCode == 13
      refresh_search = searchTeamExact this.value
    else
      refresh_search = searchTeamPrefix this.value
