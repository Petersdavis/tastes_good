//General Functions
function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    // alert(out);

    // or, if you wanted to avoid alerts...

    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
}


// Find first ancestor of el with tagName
// or undefined if not found
function upTo(el, tagName) {
  tagName = tagName.toLowerCase();

  while (el && el.parentNode) {
    el = el.parentNode;
    if (el.tagName && el.tagName.toLowerCase() == tagName) {
      return el;
    }
  }

  // Many DOM methods return null if they don't 
  // find the element they are searching for
  // It would be OK to omit the following and just
  // return undefined
  return null;
}

function PlaceHolder(){ 
/* Grab all elements with a placeholder attribute */    
var element = document.querySelectorAll('[placeholder]');    
/* Loop through each found elements */    
for (var i in element) {        
	/* If the element is a DOMElement and has the nodeName "INPUT" */        
	if (element[i].nodeType == 1 && element[i].nodeName == "INPUT") {            
		/* We change the value of the element to its placeholder attr value */            
		element[i].value = element[i].getAttribute('placeholder');            
		/* We change its color to a light gray */            
		element[i].style.color = "#777";            
		/* When the input is selected/clicked on */            
		element[i].onfocus = function (event) {                
	/* If the value within it is the placeholder, we clear it --JUST CHANGE COLOR */               
		if (this.value == this.getAttribute('placeholder')) {                   
			this.value == ""
			/* Setting default text color */                    
			this.style.color = "#000";               
		}; };            
	/* We the input is left */           
	element[i].onblur = function (event) {               
	/* If the field is empty, we set its value to the placeholder */               
		if (this.value == "") { 
			this.value = this.getAttribute('placeholder');    
			this.style.color = "#777";               
}};}}}

//Site Specific Functions\

function addEventListeners(){	
	for (i=0; i<document.forms.length; ++i){
			//id of form
			form_id = document.forms[i].id;
			form = document.getElementById(form_id);
			form.addEventListener("submit", SubmitForm, false);
	}
}

function SubmitForm(){
		
				
		var formData = new FormData ();
		var user_name = document.getElementById("username").value			
		var user_pwd = document.getElementById("password").value	
		
		formData.append ('user_name', user_name);
		formData.append ('user_pwd', user_pwd);
		formData.append ('redirect', "frontpage");
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
			//TESTING:
			//var pre = document.createElement('pre');
			//pre.innerHTML = xhr.response;
			//document.body.appendChild(pre);
			
			//Relocate
			window.location = xhr.response;
			
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'login.php', true);
		xhr.send(formData);
		
		
		
	   
}
	
	
	
	


//stop forms from changing page
document.addEventListener("DOMContentLoaded", function(event) { 
			
		PlaceHolder();
		
		
});	


	


