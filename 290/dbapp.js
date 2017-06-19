/**
 * Aimee Sullivan (sullivai) 
 * CS290-400 HW Week9
 * 28 May 2016
 * This assignment implements a (nearly) single-page web application using a combination of server-
 * side JS to interact with a database and client-side AJAX calls to display the database contents 
 * and also add, delete, and update rows.  This is the server-side code. 
 */


/*
** Many handy modules. All of this setup code is borrowed/adapted from the course notes.
*/
var express = require('express');
var mysql = require('./dbcon.js');
var handlebars = require('express-handlebars').create({defaultLayout:'main'});
var bodyParser = require('body-parser');
var redirect = require("express-redirect");

var app = express();
redirect(app);

app.engine('handlebars',handlebars.engine);
app.set('view engine','handlebars');
app.set('port', 3008);
app.use(express.static('public'));
app.use(bodyParser.urlencoded({extended:false}));
app.use(bodyParser.json());


/*
** https://www.npmjs.com/package/express-redirect
** To strip the query string off the end of the url after returning from updating, because it was ugly
** and was bugging me.  
*/
app.redirect("/?","/");


/*
** Main page, show the "Add new" form and table of exercies
*/
app.get('/',function(req,res,next){
	var context = {};
	// SELECT taken from course notes
	mysql.pool.query('SELECT * FROM workouts', function(err, rows, fields){
		if(err){
			next(err);
			return;
		}
		// display the table
		context.results = rows;
		res.render('home',context);
	});
});


/*
** Get data from db for display
*/
app.get('/select', function(req,res) {
		mysql.pool.query('SELECT * FROM workouts', function(err, rows, fields){
		if(err){
			next(err);
			return;
		}
		res.send(JSON.stringify(rows));
	});
});


/*
** Insert a new record into the database. Based on course notes.
*/
app.get('/insert',function(req,res,next){
	// get values from query and insert into new record
	mysql.pool.query("INSERT INTO workouts (`name`,`reps`,`weight`,`date`,`lbs`) VALUES (?,?,?,?,?)", 
		[req.query.name, req.query.reps, req.query.weight, req.query.date, req.query.lbs], 
		function(err, result){
		if(err){
			next(err);
			return;
		}
		res.type('text/plain');

		mysql.pool.query('SELECT * FROM workouts', function(err, rows, fields){
			if(err){
				next(err);
				return;
			}
			res.send(JSON.stringify(rows));
		});
	});
});


/*
** When "edit" button is pressed, load this new page with a form to edit the desired 
** record, based on the query parameter; function based on course notes
*/
app.get('/updateQry',function(req,res,next){
	var context = {};
	// select desired row from db
	mysql.pool.query("SELECT * FROM workouts WHERE id=?", [req.query.id], function(err,result){
		if(err){
			next(err);
			return;
		}
		if(result.length == 1){
			// save current values of the record
			var curVals = result[0];
			context = curVals;
			// if current date is a formatted date(/time) string, remove the
			// unnecessary time component in order to pass input field validation
			if (curVals.date != "0000-00-00"){
				context.date = curVals.date.toISOString().substring(0,10);
			}
			// display the Update form
			res.render('home2',context);
		}
	});
});


/*
** Submit button from Update form goes here to update the record in the database. Function adapted from 
** course notes "safe update"
*/
app.get('/update',function(req,res,next){
	// selecte record to update in db based on queried id
	mysql.pool.query("SELECT * FROM workouts WHERE id=?", [req.query.id], function(err,result) {
		if(err){
			next(err);
			return;
		}
		if(result.length == 1){
			// store the current values
			var curVals = result[0];
			// save the updated value or current value for each attribute
			mysql.pool.query("UPDATE workouts SET name=?, reps=?, weight=?, date=?, lbs=? WHERE id=? ", 
				[req.query.name || curVals.name, 
				req.query.reps || curVals.reps, 
				req.query.weight || curVals.weight, 
				req.query.date || curVals.date, 
				req.query.lbs || curVals.lbs, req.query.id],
				function(err,result){
				if(err){
					next(err);
					return;
				}
				console.log("Updated " + result.changedRows + " rows.");
			});
		}
	});
});


/*
** Delete a record from the database
*/
app.get('/delete',function(req,res,next){
	// connect to db and select row for deletion based on id query parameter
	mysql.pool.query("SELECT * FROM workouts WHERE id=?", [req.query.id], function(err,result){
		if(err){
			next(err);
			return;
		}
		// if one row was successfully returned, delete from the db
		if(result.length == 1){
			mysql.pool.query("DELETE FROM workouts WHERE id=?",
			[req.query.id],
			function(err,result){
				if(err){
					next(err);
					return;
				}
				console.log("Deleted " + result.affectedRows + " rows.");
			});
		}
	});
});


/*
** Reset table function taken from assignment spec.
*/
app.get('/reset-table',function(req,res,next){
  var context = {};
  mysql.pool.query("DROP TABLE IF EXISTS workouts", function(err){ 
    var createString = "CREATE TABLE workouts("+
    "id INT PRIMARY KEY AUTO_INCREMENT,"+
    "name VARCHAR(255) NOT NULL,"+
    "reps INT,"+
    "weight INT,"+
    "date DATE,"+
    "lbs BOOLEAN)";
    mysql.pool.query(createString, function(err){
      context.results = "Table reset";
      res.render('home',context);
    })
  });
});


/*
** The rest of this (mainly error handling) taken from the course notes.
*/
app.use(function(req,res){
	res.status(404);
	res.render('404', {layout: 'main2'});
});


app.use(function(err,req,res,next){
	console.error(err.stack);
	res.status(500);
	res.render('500', {layout: 'main2'});
});


app.listen(app.get('port'), function(){
  console.log('Express started on http://localhost:' + app.get('port') + '; press Ctrl-C to terminate.');
});

