/**
 * Aimee Sullivan (sullivai) 
 * CS290-400 HW Week9
 * 28 May 2016
 */

// database connection, taken from course notes
var mysql = require('mysql');
var pool = mysql.createPool({
	connectionLimit:10,
	host:'localhost',
	user:'student',
	password:'default',
	database:'student'
});

module.exports.pool = pool;
