//Site Specific Functions\
function searchRest(){
	if($('#community').val() !== "placeholder"){
		window.location = "list.php?community="+ $('#community').val();
	}
}



function nearestCommunity(position){
	if(position.coords.accuracy < 50000){
		var locate = {"lat":position.coords.latitude, "lng":position.coords.longitude}
		var distance = 25000;
		var myCommunity = "";
		for(x=0; x<communities.length; ++x){
			communities[x].distance = calcDistance(locate, communities[x])
			if(communities[x].distance < distance){
				distance = communities[x].distance
				myCommunity = communities[x].community
			}		
		}
		
		if(myCommunity == ""){
			alert("Sorry!  We are still getting started..  We don't have any restaurants in your community");
		}else{
			window.location="list.php?community="+ myCommunity +"&lat="+locate.lat+"&lng="+locate.lng;	
			
		}
		
		
		
	}
	
}


function restLogin(){		
	
		var formData = new FormData ();
		var user_name = document.getElementById("username").value			
		var user_pwd = document.getElementById("password").value	
		
		formData.append ('user_name', user_name);
		formData.append ('user_pwd', user_pwd);
		formData.append ('redirect', "dashboard");
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
			
			if (xhr.response=="false"){
				$('#failedLogin').show();
				//TESTING:
				
					
				
			}else{
				console.log(xhr.response);
			window.location = xhr.response;
			
			}
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'login.php', true);
		xhr.send(formData);
		
	
}
function locateFail(positionError){
	console.log(positionError.message);
	alert("Geolocation failed.  Have you enabled geolocation from in your browser settings?");
}
	

function locationSearch(){
	if(navigator.geolocation){
			navigator.geolocation.getCurrentPosition(nearestCommunity, locateFail);
	}	
}


document.addEventListener("DOMContentLoaded", function(event) {
	
	$('#community').keypress(function(e) {
        if(e.keyCode=='13') {
        	e.preventDefault();
            searchRest();
        }
      });
    $('#username').keypress(function(e) {
        if(e.keyCode=='13') {
        	e.preventDefault();
            restLogin();
        }
      });
     $('#password').keypress(function(e) {
        if(e.keyCode=='13') {
        	e.preventDefault();
            restLogin();
        }
      });
});