# Load required modules
express = require 'express'
fs = require 'fs'
git = require 'gift'
exec = require('child_process').exec
npm = require("npm");
p = {}
root = process.cwd()

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
	pkg = ''
	init: ->
		if fs.existsSync "./#{p.repository.name}/repository/"
			sg.openPackage()
		else
			sg.cloneProject()
	sshString: (pname, aname) ->
		"git@github.com:#{aname}/#{pname}.git"
	cloneProject: ->
		p.ssh = @.sshString p.repository.name, p.repository.owner.name

		exec "git clone #{p.ssh} ./#{p.repository.name}/repository/", (err) ->
			throw err if err
			sg.createDev()
	createDev: (err) ->
		repo = git "./#{p.repository.name}/repository/"

		repo.checkout 'release-1.8.0', (err) ->
			throw err if err

			sg.openPackage
	openPackage: (fn) ->
		fs.readFile "./#{p.repository.name}/repository/package.json", (err, data) ->
			throw err if err

			pkg = JSON.parse data

			sg.npmInstall()
	npmInstall: ->
		npm.load pkg, (err) ->
			throw err if err

			process.chdir "./#{p.repository.name}/repository/"

			exec 'npm install', (err) ->
				throw err if err

				console.log 'NPM install complete'

				sg.gruntDev()
	gruntDev: ->
		exec 'grunt dev', (err) ->
			throw err if err

			console.log 'BUILD COMPLETE'

			process.chdir root



sg = new stageGit();



# Declare port information
port = process.env.PORT || 3000
console.log "Listening on #{port}"
app.listen port