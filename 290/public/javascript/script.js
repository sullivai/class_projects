/**
 * Aimee Sullivan (sullivai) 
 * CS290-400 HW Week9
 * 28 May 2016
 * This assignment implements a (nearly) single-page web application using a combination of server-
 * side JS to interact with a database and client-side AJAX calls to display the database contents 
 * and also add, delete, and update rows.  This is the client-side code. 
 */


/* Modeled on course notes */
document.addEventListener("DOMContentLoaded", bindButtons);

/* Load database table */
window.onload = function() {
	var url = "select";
	var req = new XMLHttpRequest();
	req.open("GET", url, true);
	/* get JSON db rows from server */
	req.addEventListener("load",function(){
      	if (req.status >= 200 && req.status < 400) {
       		var response = JSON.parse(req.responseText);
       		// loop through each returned row and append it to the table
			document.getElementById("list").textContent = "";
       		for (var i = 0; i < response.length; i++) {
    			var newRow = makeARow(
    				response[i].name,
    				String(response[i].reps),
    				String(response[i].weight),
    				Date.parse(response[i].date),
    				response[i].lbs == 0 ? "kg" : "lbs",
    				response[i].id
    				);
    			document.getElementById("list").appendChild(newRow);
       		}
       	} else {
    		console.log("Error " + req.statusText);
    	}
	});
    req.send(JSON.stringify(url));
};



function bindButtons(){
	/*
	** Make the "Update entry" button do stuff
	*/
	// don't try to add an event listener if this button isn't here (main page)
	if(document.getElementById("updateIt")){
		// no warning about empty name here because update will take the old name if form field is blank
		document.getElementById("updateIt").addEventListener("click", function(event){
			// build query string to insert values from form
	        var url = "update";
	        url += "?name=" + document.getElementById("name").value;
	        url += "&reps=" + document.getElementById("reps").value;
	        url += "&weight=" + document.getElementById("weight").value;
	        url += "&date=" + document.getElementById("date").value;
	        url += "&lbs=" + getRadioValue("lbs");
	        url += "&id=" + document.getElementById("uid").value;
	        // send form data as query string to update function on server
	        var req = new XMLHttpRequest();
	        req.open("GET", url, true);
	        req.addEventListener("load",function(){
	        	if (req.status >= 200 && req.status < 400) {
	        		console.log(req.response);
	        	} else {
	        		console.log("Error " + req.statusText);
	        	}
	        });
	        req.send(JSON.stringify(url));
	        // let this button do what it wants because I failed to figure out
	        // how to update the db then return to the main page by myself
	        //event.preventDefault();
	    });
	}


	/*
	** Make the "Add item" button do stuff
	*/
	// don't try to add an event listener if this button isn't here (update page)
	if(document.getElementById("addNew")){
	    document.getElementById("addNew").addEventListener("click", function(event){
	    	// require that name have a value
			if (document.getElementById("name").value == "") {
				alert("You must provide the exercise name.");
			} else {
				// build query string to insert values from form
		        var url = "insert";
		        url += "?name=" + document.getElementById("name").value;
		        url += "&reps=" + document.getElementById("reps").value;
		        url += "&weight=" + document.getElementById("weight").value;
		        url += "&date=" + document.getElementById("date").value;
		        url += "&lbs=" + getRadioValue("lbs");
		        // clear form fields
		        document.getElementById("createNew").reset();
		        // send form data as query string to insert function on server
		        var req = new XMLHttpRequest();
		        req.open("GET", url, true);
		        req.addEventListener("load",function(){
		        	if (req.status >= 200 && req.status < 400) {
		        		var response = JSON.parse(req.responseText);
		        		// get newest record index and make a new row with that record's data
		    			var i = response.length - 1;
		    			var newRow = makeARow(
		    				response[i].name,
		    				String(response[i].reps),
		    				String(response[i].weight),
		    				Date.parse(response[i].date),
		    				response[i].lbs == 0 ? "kg" : "lbs",
		    				response[i].id
		    				);
		    			// append the new row to the table
        				document.getElementById("list").appendChild(newRow);
		        	} else {
		        		console.log("Error " + req.statusText);
		        	}
		        });
		        req.send(JSON.stringify(url));
			}
			// stop the form from trying to go somewhere else
	        event.preventDefault();
	    });
	}
}


/*
** Add the data cells for each row of the table of exercises.
*/
function makeARow(name,reps,weight,date,lbs,id){
	// Unbelievable efforts required to format a date string in this stupid language
	var options = {};
	options.timeZone = "UTC";

	// create cells mostly using the function from Eloquent JS; buttons required a little extra effort
	var newRow = create("tr", 
		create("td", name),
		create("td", reps),
		create("td", weight),
		create("td", new Date(date).toLocaleDateString("en-US",options)),
		create("td", lbs),
		create("td", createEdit()),
		create("td", createDelete())
	);
	newRow.id = id;

	return newRow;
}


/* 
** Function to create nodes of type taken from Eloquent JS Ch 13 http://eloquentjavascript.net/13_dom.html
*/
function create(type) {
    var node = document.createElement(type);
    for (var i = 1; i < arguments.length; i++) {
        var child = arguments[i];
        if (typeof child == "string")
            child = document.createTextNode(child);
        node.appendChild(child);
    }
    return node;
}


/*
** Create a delete button
*/
function createDelete() {
	// make the button
	var node = document.createElement("input");
	node.type = "submit";
	node.value = "Delete";
	node.name = "Delete";

	node.addEventListener("click", function(event) {
		// delete record from the db
	 	del(this.parentNode.parentNode.id);
	 	// remove the row from the display
	 	this.parentNode.parentNode.remove();
	 	event.preventDefault();
	 });

	return node;
}


/*
** Create a form and edit button
*/
function createEdit() {
	// make form, assign method and action
	var node = document.createElement("form");
	node.action = "/updateQry";
	node.method = "get";

	// make a hidden input and append it to the form
	var nodeHidden = document.createElement("input");
	nodeHidden.type = "hidden";
	nodeHidden.name = "id";
	node.appendChild(nodeHidden);

	// create a button
	var nodeBtn = document.createElement("input");
	nodeBtn.type = "submit";
	nodeBtn.value = "Edit";

	// assign value of record id to hidden input so it can be sent by the form
	nodeBtn.addEventListener("click", function(event) {
	 	this.parentNode.firstElementChild.value = this.parentNode.parentNode.parentNode.id;
	 });

	// add button to the form
	node.appendChild(nodeBtn);

	return node;
}


/*
** Function to delete a row from the database
*/
var del = function(p) {
	// query string with id of row to delete
	var url = "delete";
	url += "?id=" + p;

	var req = new XMLHttpRequest();
	req.open("get", url, true);
	req.send();

	req.addEventListener("load", function(){
		if (req.status >= 200 && req.status < 400) {
			//var response = JSON.parse(req.responseText);
		}
	});
};


/*
** Function from http://stackoverflow.com/questions/604167/how-can-we-access-the-value-of-a-radio-button-using-the-dom
** Returns the selected radio option value
*/
function getRadioValue(theRadioGroup)
{
    var elements = document.getElementsByName(theRadioGroup);
    for (var i = 0, l = elements.length; i < l; i++)
    {
        if (elements[i].checked)
        {
            return elements[i].value;
        }
    }
}