//General Functions
function calcDistance (pointA, pointB){
	    var radlat1 = Math.PI * pointA.lat/180;
        var radlat2 = Math.PI * pointB.lat/180;
        var radlon1 = Math.PI * pointA.lng/180;
        var radlon2 = Math.PI * pointB.lng/180;
        var theta =  ((1000*pointA.lng)-(1000*pointB.lng))/1000;
        var radtheta = Math.PI * theta/180;
        var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
        dist = Math.acos(dist);
        dist = dist * 180/Math.PI;
        dist = dist * 60 * 1.1515;
        dist = dist * 1.609344 
        return dist
	
}

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

function trueClone(orgNode){

	 var orgNodeEvenets = $(orgNode).find('*');
	 var cloneNode = orgNode.cloneNode( true );
	 var cloneNodeEvents = cloneNode.getElementsByTagName('*');
	
	 var allEvents = new Array('onabort','onbeforecopy','onbeforecut','onbeforepaste','onblur','onchange','onclick',
	'oncontextmenu','oncopy','ondblclick','ondrag','ondragend','ondragenter', 'ondragleave' ,
	'ondragover','ondragstart', 'ondrop','onerror','onfocus','oninput','oninvalid','onkeydown',
	'onkeypress', 'onkeyup','onload','onmousedown','onmousemove','onmouseout',
	'onmouseover','onmouseup', 'onmousewheel', 'onpaste','onreset', 'onresize','onscroll','onsearch', 'onselect','onselectstart','onsubmit','onunload');
	
	 /*
	 // The node root
	 for( var j=0; j<allEvents.length ; j++ ){
	 	 eval('if( orgNode.'+allEvents[j]+' ) cloneNode.addEventListener("'+allEvents[j].replace("on", "")+'", orgNode.'+allEvents[j]+')')
	 	 	 }
	
	 
	 
	 
	 // Node descendants
	 for( var i=0 ; i<orgNodeEvenets.length ; i++ ){
		  for( var j=0; j<allEvents.length ; j++ ){
			  eval('if( orgNodeEvenets[i].'+allEvents[j]+' ) cloneNodeEvents[i].addEventListener("'+allEvents[j].replace("on", "")+'", orgNodeEvenets[i].'+allEvents[j]+')');
		  }
		  		 
		  
	 }
	
	 */
	 
	 return cloneNode;

}

if (!Date.now) {
    Date.now = function() { return new Date().getTime(); }
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

function removeClass(element, classname) {
    element.className = element.className.replace(new RegExp('(?:^|s)' + classname + '(?!S)'), '');
}

function removeByAttr(arr, attr, value){
    var i = arr.length;
    while(i--){
       if( arr[i] 
           && arr[i].hasOwnProperty(attr) 
           && (arguments.length > 2 && arr[i][attr] === value ) ){ 

           arr.splice(i,1);

       }
    }
    return arr;
}


function addClass(element, classname) {
    var cn = element.className;
    cn = cn + " " +classname;
    element.className= cn;
};

function stopEvent(event){
 	if (event.stopPropagation) {
      event.stopPropagation();
    }
    //IE8 and Lower
    else {
      event.cancelBubble = true;
    }
}


function PlaceHolder(){ 
/* Grab all elements with a placeholder attribute */    
var element = document.querySelectorAll('[placeholder]');    
/* Loop through each found elements */    
for (var i in element) {        
	/* If the element is a DOMElement and has the nodeName "INPUT" */        
	if (element[i].nodeType == 1 && element[i].nodeName == "INPUT") {            
		/* We change the value of the element to its placeholder attr value */            
		if(element[i].value == ""){
			element[i].value = element[i].getAttribute('placeholder');     
		}
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

//Local Storage SHIM
if (!window.localStorage) {
  Object.defineProperty(window, "localStorage", new (function () {
    var aKeys = [], oStorage = {};
    Object.defineProperty(oStorage, "getItem", {
      value: function (sKey) { return sKey ? this[sKey] : null; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "key", {
      value: function (nKeyId) { return aKeys[nKeyId]; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "setItem", {
      value: function (sKey, sValue) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=" + escape(sValue) + "; expires=Tue, 19 Jan 2038 03:14:07 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "length", {
      get: function () { return aKeys.length; },
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "removeItem", {
      value: function (sKey) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    this.get = function () {
      var iThisIndx;
      for (var sKey in oStorage) {
        iThisIndx = aKeys.indexOf(sKey);
        if (iThisIndx === -1) { oStorage.setItem(sKey, oStorage[sKey]); }
        else { aKeys.splice(iThisIndx, 1); }
        delete oStorage[sKey];
      }
      for (aKeys; aKeys.length > 0; aKeys.splice(0, 1)) { oStorage.removeItem(aKeys[0]); }
      for (var aCouple, iKey, nIdx = 0, aCouples = document.cookie.split(/\s*;\s*/); nIdx < aCouples.length; nIdx++) {
        aCouple = aCouples[nIdx].split(/\s*=\s*/);
        if (aCouple.length > 1) {
          oStorage[iKey = unescape(aCouple[0])] = unescape(aCouple[1]);
          aKeys.push(iKey);
        }
      }
      return oStorage;
    };
    this.configurable = false;
    this.enumerable = true;
  })());
}

function validatePrice(value, placeholder){
		placeholder = placeholder || 0;			
			if(/[\d]/.test(value)){
				var goodvalue = "$"
				goodvalue = goodvalue + /[\d]+[.]{0,1}[\d]{0,2}/.exec(value)[0];
				
				if(!/[.]/.test(goodvalue)){
					goodvalue=goodvalue + ".00"
				}
				if(!/[.][\d]{2}/.test(goodvalue)){
					goodvalue=goodvalue + "0"
					if(!/[.][\d]{2}/.test(goodvalue)){
						goodvalue=goodvalue + "0"	
						
					}
				}
				
				return goodvalue
			} else {return placeholder;}	
}