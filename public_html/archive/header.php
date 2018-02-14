<?php function TopMenu(){
	global $gUserid;
	//user not logged in Show: nothing
	if ($gUserid == 0){
	}
	else {
		echo '<li><a href="">My Restaurants</a></li>';
		echo '<li><a href="">My Orders</a></li>';
	}
	//user is client  Show: my restaurants // my orders
}

function TopRightMenu (){
	global $gUserid;
	global $gProd;
		
		
		echo '<div class = "dropdown">';
			echo '<button class="btn dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
			echo '<span class="glyphicon glyphicon-ice-lolly"></span>';
			echo '</button>';
			echo '<ul id = "collapseOne" class="dropdown-menu" aria-labelledby="dropdownMenu1">';
			echo '<li><a href="#">Preferences</a></li>';
			echo '<li><a href="#">Logout</a></li>';
			echo '</ul>';
		
		
		echo '</div>';
		
	
}
?>

<div class = "container container-fluid navbar-fixed-top" style="width:100%;background-color:rgb(172, 232, 213);padding-top:5px;padding-bottom:5px;" >
   <div class="navbar-inner">
		<div class="nav navbar-header">
			<a href="/"> <img class="image" src ="/images/logo/tg.png"> </image></a>
		</div>
		<button class ="btn" id="dropdown_btn" style="background-color:rgba(0,0,0,0);color:rgba(60,38,38,100);position:absolute;top:10px;right:25px;"><span class="glyphicon glyphicon-align-justify" style="font-size:30px;" aria-hidden="true"></span></button>
		
	</div>
</div>

<div id="navbar_dropdown" class="container main col-xs-12" hidden style="background-color: rgba(240,240,240,.9);background-clip:padding-box; border-color: rgba(0, 0, 0, 0.2);box-shaddow:0 3px 8px rgba(0, 0, 0, .3); border-style:solid; border-width:0.8px; border-radius:6px; width:20%;right:0;min-width:100px;position:absolute;top:73px; z-index:5000;padding:12px;">
<button id="dropdown_login" class="btn btn-default btn-block">Login</button>
<button id="dropdown_logout" class="btn btn-default btn-block">Sign Out</button>
</div>




