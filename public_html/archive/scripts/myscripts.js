//Global Variable Initializations
var CategoryTotal=0
var ItemTotal=0
var ExtraTotal=0
var CurrentFocus = ""



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
function MakeButton (name, options){
	 var options = options || {};
	 var btnClass = options.btnClass || "btn";
	 var btnValue = options.btnValue || name
	 const btnTarget = options.btnTarget || ""
	 const callFunction = options.callFunction || ""
	 callFunction = toString('"' + callFunction + '(' + btnTarget +')' + '"');
	
	 retString = '<button type="button" class = "' + btnClass + '" name = "' + name + '" onclick = ' + callFunction + '>' + btnValue + '</button>';
	 return retString;
	 
	
	
}


function ConstructMenu (){
	var form = document.getElementById("rest_menu");
	var div = document.getElementById("rest_categories");
	//if menu is available first construct the menu from arrays
	
	var newItem = document.createElement('div');
	newItem.name = "CategoryButtons";
	newItem.id = "CategoryButtons";
	newItem.innerHTML = MakeButton (addCategory, {btnValue: "Add New Category", callFunction: "addCategory"});
	form.appendChild(newItem);

}

function ConstructExtras (){
	
}


function addCategory(){
	var form = document.getElementById("rest_menu");
	var div = document.getElementById("rest_categories");
	 	
	var newDiv = document.createElement('div');
	newDiv.id = "BlankCategory" 
	newDiv.name = "BlankCategory"
		
	var newItem = document.createElement('input');
	newItem.id = "category[Blank][category]"
	newItem.name = "category"; 
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('input');
	newItem.id = "category[Blank][hierarchy]"
	newItem.name = "hierarchy";
	newItem.placeholder = "blank"
	newItem.disabled = "disabled"
	//newItem.class = "hidden" ;
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement ('br');
	newItem.id = "brCategory"
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('button');
	newItem.class = "btn";
	newItem.type = "button";
	newItem.name = "SaveCategory";
	newItem.innerHTML = "Save Category";
	newItem.onclick = "SaveCategory('Blank')";
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('button');
	newItem.class = "btn";
	newItem.type = "button";
	newItem.name = "EditCategory";
	newItem.innerHTML = "Edit Category";
	newItem.onclick = "EditCategory('Blank')";
	newItem.disabled = "disabled" 
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('button');
	newItem.type = "button";
	newItem.class = "btn";
	newItem.name = "DeleteCategory";
	newItem.innerHTML = "Delete Category";
	newItem.onclick = "DeleteCategory('Blank')";
	newItem.disabled = "disabled" 
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('button');
	newItem.type = "button";
	newItem.class = "btn";
	newItem.name = "PromoteCategory";
	newItem.innerHTML = "Promote Category";
	newItem.onclick = "PromoteCategory('Blank')";
	newItem.disabled = "disabled" 
	newDiv.appendChild(newItem);
	
	window.BlankTotal = window.BlankTotal + 1;
	div.appendChild(newDiv);
	
	

	
}

function SaveCategory(id){
	const str = "";
	var div = document.getElementById(id);
	var kidInputs = div.getElementsByTagName('input');
	var kidButtons = div.getElementsByTagName('button');
	div.id = div.firstChild.value
	
	
	if (id == "Blank"){
		//then this is a new category
						
		//loop the inputs
		for (i=0;i<kidInputs.length;++i){
			
			switch (kidInputs[i].name){
				case "hierarchy":
					kidInputs[i].value =  window.CategoryTotal;
					//without break should pass through to default
				
				default:
					
					kidInputs[i].disabled = "disabled"
					str = kidInputs[i].id; 
					str = str.replace("Blank", window.CategoryTotal);				
					kidInputs[i].id = str;
			}
		}
		
		//delete the <br>
		var br = div.getElementsByTagName('brCategory')
		br[0].parentNode.removeChild(br[0]);
		
		//loop the buttons
		
		for (i=0;i<kidButtons.length;++i){
			switch(kidButtons[i].name){
		
			case "SaveCategory":
				kidButtons[i].disabled = "disabled"
				str = kidButtons[i].onClick; 
				str = str.replace("Blank", div.id);				
				kidInputs[i].onClick = str;
				break	
					
			default: 
				kidButtons[i].removeAttribute("disabled");
				str = kidButtons[i].onClick; 
				str = str.replace("Blank", div.id);				
				kidInputs[i].onClick = str;
				
				
			}
		}
		
		newItem = document.createElement('button'); 
		newItem.name = "AddItem"
		newItem.innerHTML = "Add Item to " + div.id;
		newItem.onClick = "AddItem('" + div.id + "')";
		div.appendChild(newItem);
		
		window.CategoryTotal = window.CategoryTotal + 1
	
	} else {
		//rename the container	
		
		for (i=0;i<kidInputs.length;++i){
			kidInputs[i].disabled = "disabled"	
		}
	
		
		//loop the buttons
		for (i=0;i<kidButtons.length;++i){
			switch(kidButtons[i].name){
		
			case "SaveCategory":
				kidButtons[i].disabled = "disabled"
				str = kidButtons[i].onClick; 
				str = str.replace(id, div.id);				
				kidInputs[i].onClick = str;
				break	
					
			default: 
				kidButtons[i].removeAttribute("disabled");
				str = kidButtons[i].onClick; 
				str = str.replace(id, div.id);				
				kidInputs[i].onClick = str;
				
				
				} 
		
		}
	}
}


function EditCategory(category){
	var div = document.getElementById(category);
	div.firstChild.removeAttribute("disabled");
	var savecategory = div.getElementsByName("SaveCategory");
	savecategory.removeAttribute ("disabled");
	var editcategory = div.getElementsByName("EditCategory");
	editcategory.disabled = "disabled";
}

function DeleteCategory(id){
	window.CategoryTotal = window.CategoryTotal - 1;
	div = document.getElementById(id);
	div.parentNode.removeChild(div);
	
	//must also cascade the hierarchy of other categories
	
}

function PromoteCategory(category){
	var div = document.getElementById(category);
	if (div == div.parentNode.firstChild){
		alert ('cannot promote' + div.id + 'because it is the first child');
	} else {
		//physically move the elements
		var target = div.previousSibling;
		div.parentNode.insertBefore(div, target);
		
		//swap their hierarchies
		var DivValue = div.getElementsByName(hierarchy).value;
		var TargetValue = div.getElementsByName(hierarchy).value;
		DivValue = DivValue - 1
		TargetValue = TargetValue + 1
		
  
	}
}


function AddItem(category){
	
	//create container div id will be Item[x]
	
	var div = document.getElementById(category);
	
	var newDiv = document.createElement('div');
	newDiv.id = "BlankItem";
	newDiv.name = "BlankItem";
	
	//create inputs
	var newItem = document.createElement('input');
	newItem.id = "item[Blank][id]";
	newItem.name = "item_id"; 
	newItem.type = "text";
	newItem.disabled = "disabled"
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('input');
	newItem.id = "item[Blank][product]";
	newItem.name = "product"; 
	newItem.type = "text";
	newItem.placeholder = "Item Name";
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('input');
	newItem.id = "item[Blank][description]";
	newItem.name = "description"; 
	newItem.type = "text";
	newItem.placeholder = "Description";
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('input');
	newItem.id = "item[Blank][category]";
	newItem.name = "category"; 
	newItem.type = "text";
	newItem.value = category;
	newItem.disabled = "disabled"
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('input');
	newItem.name = "description"; 
	newItem.type = "text";
	newItem.placeholder = "$0.00";
	newDiv.appendChild(newItem);
	
	//<br>
	var newItem = document.createElement ('br');
	newItem.id = "brItem"
	newDiv.appendChild(newItem);
	
	//create buttons. 
	 
	var newItem = document.createElement('button');
	newItem.class = "btn";
	newItem.type = "button";
	newItem.name = "SaveItem";
	newItem.innerHTML = "Save Item";
	newItem.onclick = "SaveItem('Blank')";
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('button');
	newItem.class = "btn";
	newItem.type = "button";
	newItem.name = "EditItem";
	newItem.innerHTML = "Edit Item";
	newItem.onclick = "EditItem('Blank')";
	newItem.disabled = "disabled" 
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('button');
	newItem.class = "btn";
	newItem.type = "button";
	newItem.name = "DeleteItem";
	newItem.innerHTML = "Delete Item";
	newItem.onclick = "DeleteItem('Blank')";
	newItem.disabled = "disabled" 
	newDiv.appendChild(newItem);
	
	var newItem = document.createElement('button');
	newItem.class = "btn";
	newItem.type = "button";
	newItem.name = "AddExtra";
	newItem.innerHTML = "Add an Extra to Item";
	newItem.onclick = "AddExtra('Blank')";
	newItem.disabled = "disabled" 
	newDiv.appendChild(newItem);
	

	div.appendChild(newDiv);

}

function SaveItem(id){
	const str = "";
	const ItemTotal = window.ItemTotal
	var div = document.getElementById(id);
	var kidInputs = div.getElementsByTagName('input');
	var kidButtons = div.getElementsByTagName('button');
	
	
	
	if (id == "BlankItem"){
		
		
		//then this is a new item
		div.id = "item[" + ItemTotal + "]";
		
		//loop the kid inputs
		
		for (i=0;i<kidInputs.length;++i){
			kidInputs[i].disabled = "disabled"
			
			switch (kidInputs[i].name){
				case "item_id":
					kidInputs[i].value =  ItemTotal;
					//without break should pass through to default
				
				default:
					
									
					str = kidInputs[i].id; 
					str = str.replace("Blank", ItemTotal);				
					kidInputs[i].id = str;
			}
		}
		//delete the <br>
		var br = div.getElementsByTagName('brItem')
		br[0].parentNode.removeChild(br[0]);
		
		//fix the buttons

		for (i=0;i<kidButtons.length;++i){
			
			str = kidButtons[i].onClick; 
			str = str.replace(id, div.id);				
			kidInputs[i].onClick = str;
			
			
			switch(kidButtons[i].name){
			
			case "SaveItem":
				kidButtons[i].disabled = "disabled";
				
				str = kidInputs[i].id; 
				str = str.replace("Blank", div.id);				
				kidInputs[i].id = str;
				break
				
			default:	
				
				kidButtons[i].removeAttribute("disabled");
				str = kidInputs[i].id; 
				str = str.replace("Blank", div.id);				
				kidInputs[i].id = str;
			}	
				
				
		}
		
		
		window.ItemTotal = window.ItemTotal + 1			
	
	
	}else{
		//This is not a new item
	
		//disable the inputs
		for (i=0;i<kidInputs.length;++i){
			kidInputs[i].disabled = "disabled"	
		}
		
		
		//loop the buttons
		for (i=0;i<kidButtons.length;++i){
			switch(kidButtons[i].name){
		
			case "SaveItem":
				kidButtons[i].disabled = "disabled"
				str = kidButtons[i].onClick; 
				str = str.replace(id, div.id);				
				kidInputs[i].onClick = str;
				
				break	
					
			default: 
				kidButtons[i].removeAttribute("disabled");
				str = kidButtons[i].onClick; 
				str = str.replace(id, div.id);				
				kidInputs[i].onClick = str;
				
				
			} 
		
		}
		
	}
}
	
		


function EditItem(id){
	var div = document.getElementById(id);
	var kidInputs = div.getElementsByTagName('input');
	var kidButtons = div.getElementsByTagName('button');

	for (i=0;i<kidInputs.length;++i){
		kidInputs[i].removeAttribute("disabled");
		}
	
	for (i=0;i<kidButtons.length;++i){
	 kidButtons[i].removeAttribute("disabled");
		switch(kidButtons[i].name){
			case "EditItem":
			kidInputs[i].disabled = "disabled";
			break;
		
			default:
			
		}	
	}
}


function DeleteItem(id){
	var div = document.getElementById(id);
	div.parentNode.removeChild(div);
	window.ItemTotal = window.ItemTotal - 1;
	
}




function CreateExtra(item){
	
}
function AddExtra(div){
	
	
}

function addInput(divName){
          var newdiv = document.createElement('div');
          newdiv.innerHTML = "Category:  <br><input type='text' name='category'></input>";
          document.getElementById(divName).appendChild(newdiv); 
}


function addEventListeners(){	
	for (i=0; i<document.forms.length; ++i){
			//id of form
			form_id = document.forms[i].id;
			form = document.getElementById(form_id);
			form.addEventListener("submit", submitform, false);
	}
}

function submitform(event){

		event.preventDefault();
		
		form = event.target;
		form_id = event.target.id;
		form_type = form.name;
				
		inputs = [];
		values = [];
		inputs = form.getElementsByTagName("input");
		
		var formData = new FormData ();
					
		for(var i=0;i<inputs.length;i++){
		
		input=inputs[i];
		
			
		if(input.type == 'file'){
			if(input.files.length !== 0){
			
			  formData.append (input.name, input.files[0], input.files[0].name);
			  alert (inputs[i].files[0].name);
			}
		  }  else {
			  if(input.type !== 'submit'){
				if (input.value != input.placeholder){
				  values.push ([input.name, input.value, input.id])  
				 
				}
			  
			  }
		  }		  
		};
		
		valuesJSON = JSON.stringify(values);
		formData.append ('submission', valuesJSON);
		formData.append ('form_type', form_type);
		formData.append ('rest_id', window.rest_id);
		
				
		
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
			var pre = document.createElement('pre');
			pre.innerHTML = xhr.response;
			document.body.appendChild(pre);
			//get global variables
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'uploader.php', true);
		xhr.send(formData);
		
		//Action to the Different Forms
		switch(form_type) {
	
			case "restaurant":
				
				edit_button = document.getElementById('edit_button');
				save_button = document.getElementById('save_button');
				
				y = document.getElementsByTagName("input");
				for(i=0; i < y.length; i++){
				document.removeChild(y[i]);
				}
				
				
				save_button.setAttribute("class", "hidden");
				edit_button.removeAttribute("class");
				break;
				
				
			case "new_restaurant":
				document.getElementById('new_restaurant').setAttribute("class", "hidden");
				document.getElementById('rest_menu').removeAttribute("class");
				document.getElementById('rest_extras').removeAttribute("class");
				ConstructMenu();
				ConstructExtras();
				
				
				
				break;
			
			
	   
		}
	  
}
	
	
	


//stop forms from changing page
document.addEventListener("DOMContentLoaded", function(event) { 
			
		PlaceHolder();
		
		
		rest_id = document.getElementById("rest_id").innerHTML;
		
		//Testing to jump ahead to menu setting. 
		document.getElementById('new_restaurant').setAttribute("class", "hidden");
		document.getElementById('rest_menu').removeAttribute("class");
		document.getElementById('rest_extras').removeAttribute("class");
		ConstructMenu();
		ConstructExtras();
		
		//fetch rest ID with window.rest_id  
		
		addEventListeners();
});	

function EditRest (form_id){
	//need to rewrite code
	edit_button = document.getElementById('edit_button');
	save_button = document.getElementById('save_button');
	form = document.getElementById(form_id);
	image = document.getElementById('image')
	inputs = document.getElementsByClassName('edit')
 	
	edit_button.setAttribute("class", "hidden");
	save_button.removeAttribute("class");
	
			x = document.createElement("input");
			x.type =  "file" ;
			x.name = "file" ;
			x.id = "upload_image";
			image.appendChild(x) 
		
		
	for(i=0; i < inputs.length; i++){
		inputs[i].innerHTML = '<input type = text name ="' + inputs[i].id + '" placeholder = "' + inputs[i].innerHTML +'"></input>';
	}
		

	PlaceHolder();
}
	


