# Load required modules
express = require 'express'
fs = require 'fs'
git = require 'gift'
exec = require('child_process').exec
p = {}

# Declare StageGit app
app = express.createServer express.logger()


# Response to request
app.get '/', (request, response) ->

	# Temporary local file to load
	fs.readFile 'payload.json', (err, data) ->
		throw err if err 

		p = JSON.parse data

		# Start stageGit Class
		sg.init()

	response.send 'running'




# Our object for various functions
class stageGit
	init: ->
		sg.makeDir()
	sshString: (pname, aname) ->
		"git@github.com:#{aname}/#{pname}.git"
	makeDir: ->
		fs.mkdir p.repository.name, ->
			process.chdir p.repository.name
			fs.mkdir 'repository', ->
				process.chdir 'repository'

				console.log 'hellllooooo'

				sg.cloneProject()
	cloneProject: ->
		console.log p

		p.ssh = @.sshString p.repository.name, p.repository.owner.name

		exec "git clone #{p.ssh} ./", ->
			console.log 'test'

sg = new stageGit();



# Declare port information
port = process.env.PORT || 3000
console.log "Listening on #{port}"
app.listen port