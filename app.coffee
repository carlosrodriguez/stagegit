# Load required modules
express = require 'express'
fs = require 'fs'
git = require 'gift'
exec = require('child_process').exec

# Declare StageGit app
app = express.createServer express.logger()


# Response to request
app.get '/', (request, response) ->
	sg = new stageGit();

	console.log sg

	# Temporary local file to load
	fs.readFile 'payload.json', (err, data) ->
		throw err if err 

		p = JSON.parse data

		# Make the project folder & cd to it
		# Using callbacks to make sure folders get created before chdir
		fs.mkdir p.repository.name, ->
			process.chdir p.repository.name
			fs.mkdir 'repository', ->
				process.chdir 'repository'

				p.ssh = sg.sshString p.repository.name, p.repository.owner.name

				exec "git clone #{p.ssh} ./", ->
					console.log 'test'

				

	response.send 'running'




# Our object for various functions
stageGit = ->
stageGit.prototype = {
	sshString: (pname, aname) ->
		"git@github.com:#{aname}/#{pname}.git"
}
		



# Declare port information
port = process.env.PORT || 3000
console.log "Listening on #{port}"
app.listen port