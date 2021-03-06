<?php include '../boilerplate.php'; 

if(!isset($_SESSION['pass'])||!$_SESSION['pass']){
	header('Location: ../');
}

checkProduction();?>
<?php include '../dbconnect.php'; 

if(isset($_SESSION['rest_id'])){
	$rest_id = $_SESSION['rest_id'];
}else{
	exit("please login");
}

	
$file = __FILE__;
$rest = new restaurant ();
$rest->grabRest($rest_id);
$rest->grabCoupons($rest_id);
$rest->grabSerial($rest_id);  


html_Header("Restaurant Dashboard"); 
include 'header.php';

?>


<!--Icons-->
<script src="js/lumino.glyphs.js"></script>

<!--Variables -->

<script> rest_id = "<?php echo $rest_id; ?>" </script>
<script>Extras = <?php echo json_encode($rest->menu->extras); ?> </script>
<script>lastExtra = <?php echo $rest->menu->lastExtra; ?> </script>

<!--MyScripts -->

<script src="../scripts/coupons.js"></script>
<script src="../scripts/basic.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>


<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->

<div class="main sidebar col-sm-3 col-lg-2 sidebar" style="margin-left:10px;">
	<?php include 'sidebar_content.php' ?>
</div><!--/.sidebar-->

<div class = "row">	
	<div class="col-xs-9 col-sm-6 col-sm-offset-3 col-lg-7 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">
		<div class = "main" id="coupon_builder" style="background-color:#f0ffff;">
			<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Coupon Builder: <button onclick = "$('#tutorial').modal('show');" class = "btn btn-primary" style="float:right;font-size:25px" >?</button></h3>
			<h2 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Settings: </h2>
			
			<h2 style="font-size:20px">Would you like to promote the coupon on our website?</h2>	
			<div class = "row" id="coupon_details" style="margin-left:20px; margin-right:20px;">
				
				<div class = "col-xs-2">
					<input name="public_coupon" id = "public_coupon" value="public" type="radio" checked/>
				</div>
				<div class = "col-xs-4">
					<label for = "public_coupon">Public Coupon</label>
				</div>
				<div class = "col-xs-2">
					<input name="public_coupon" id = "private_coupon" value="private" type="radio"/>
				</div>
				<div class = "col-xs-4">
					<label for = "public_coupon">Private Coupon</label>
				</div>
			</div>
			<h2 style="font-size:20px">When does the coupon expire?</h2>
			<div class = "row" id="coupon_details2" style="margin-left:20px; margin-right:20px;">	
				<div class = "col-xs-1  no-padding">
					<input name="coupon_expire" id="expire_3M" value="3" type="radio" checked/>
				</div>
				<div class = "col-xs-2 no-padding">
					<label for="expire_3M">3 Months </label>
				</div>
				<div class = "col-xs-1  no-padding">
					<input name="coupon_expire" id="expire_6M" value="6" type="radio"/>
				</div>
				<div class = "col-xs-2 no-padding">
					<label for="expire_6M">6 Months </label>
				</div>
				<div class = "col-xs-1 no-padding">
					<input name="coupon_expire" id="expire_9M" value="9" type="radio"/>
				</div>
				<div class = "col-xs-2 no-padding">
					<label for="expire_9M">9 Months </label>
				</div>
				<div class = "col-xs-1 no-padding">
					<input name="coupon_expire" id="expire_12M" value="12" type="radio"/>
				</div>
				<div class = "col-xs-2 no-padding">
					<label for="expire_12M">One Year </label>
				</div>	
				
			</div>
			
			<h2 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Type: </h2>
			<div class = "row">
				<div class = "col-xs-6" style = "text-align:center;line-height:40px;">
					<input name="coupon_type" id = "type_discount" value="discount" type="radio" checked/>
					<label for = "type_discount">Percent Discount</label>
				</div>
				<div class = "col-xs-6" style = "text-align:center;line-height:40px;">
					<input name="coupon_type" id = "type_item" value="item" type="radio"/>
					<label for = "type_item">Special Item(s)</label>
				</div>
			</div>
			<div class = "row" id="discount">
				<div class ="col-xs-12">
					<h2 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Discount: </h2>
					<div class = "row" style="margin-left:15px">
						<div class="col-xs-4 no-padding" style=" line-height:30px;font-size:18px;"> <label for="discount_percent" >Percentage Discount:</label> </div>
						<div class="col-xs-8"> <input class = "form-control" type="text" id="discount_percent" placeholder="10%" disabled/> </div>
					</div>
				</div>
				<div class="col-xs-12">
					<div class= "row" style="margin:10px;">
						<div class="col-xs-1 no-padding" ><input name="discount_percent" id = "10_percent" value="0.1" type="radio" checked/></div>
						<div class="col-xs-2 no-padding" ><label for="10_percent">10 percent</label></div>
						<div class="col-xs-1 no-padding"><input name="discount_percent" id = "15_percent" value="0.15" type="radio" /></div>
						<div class="col-xs-2 no-padding" ><label for="15_percent">15 percent</label></div>
						<div class="col-xs-1 no-padding"><input name="discount_percent" id = "20_percent" value="0.2" type="radio" /></div>
						<div class="col-xs-2 no-padding" ><label for="20_percent">20 percent</label></div>
						<div class="col-xs-1 no-padding"><input name="discount_percent" id = "25_percent" value="0.25" type="radio" /></div>
						<div class="col-xs-2 no-padding" ><label for="25_percent">25 percent</label></div>
					</div>
				</div>
			</div>
			
			<div class="row" id="item" style="display:none;">
				<div class = "col-xs-12"><h2 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Item Settings: </h2></div>
				<div  class = "col-xs-8">
					<div class = "row">
						<label class = "col-xs-4" for="coupon_title" > Title: </label>
						<input class = "col-xs-8" id = "coupon_title" class= "form-control" type = "text" placeholder = "Your Title Here" maxlength="20"></input>
					</div>
					<div class = "row">
						<label class = "col-xs-4" for ="coupon_price"> Price: </label>
						<input class = "col-xs-8" id = "coupon_price" class= "form-control" type = "text" placeholder = "$0.00"></input>
					</div>
					<ol>
					<li>Select a Title (This gets printed on the coupon)</li>
					<li>Set the base price.</li>
					<li>Create Optionals (If the item[s] require options: Create the Optional on the right)</li>
					<li>Drag Optionals to attach them to the item.  
					</ol>
				</div>
				<div class = "col-xs-4">
					<ul class="list-group" id="coupon_Docket" name="coupon_Docket"  style="min-height:130px;background-color:lightgrey;border-style: dashed;"><li class="placeholder list-group-item" style="color:black;"> Drag Optional Questions Here:  </li></ul>
				</div>
			</div>
			<div class = "row">
				<div class = "col-xs-8 col-xs-offset-2">
					<button id="create_coupon" class= "btn btn-block btn-primary" style="height:50px;font-size:25px;">Save Coupon.</button>
				</div>
			</div>
			
		</div>
		
		<div class = "main" style="background-color:#f0ffff;margin-top:20px;">
			<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Existing Coupons:</h3>
			<ul id="coupon_list" class = "list-group">
			<?php foreach($rest->coupons as $coupon) {	?>
				<li class = "list-group-item" id="coupon_<?php echo $coupon->id; ?>" style = "height:130px;">
					<div style="position:relative; width:175px;">
						<a href = "<?php echo $coupon->link; ?>" style = "position:absolute; top:0; left:0; width:175px; height:100px; z-index:1000;"></a>
						<img style = "position:absolute; top:0; left:0; width:175px; height:100px;" src="/images/biz_card_blank.png"/>
						<h2 style = "position:absolute; top:0px;line-height:15px; left:5px;font-size:15px; width:160px; text-align:center;" > <?php echo $coupon->title;  if($coupon->type =="item"){echo ': <span style="color: #633E26;">$'. $coupon->price .'</span>';} ?> </h3>
						<img style = "position:absolute; top:22px; left:0;height:56px;width:56px;" src="<?php echo $rest->sm_image; ?>"/>
						<h3 style  = "position:absolute; top:55px; margin-top:0; left:67px;font-size:15px; width:100px; text-align:center;"><?php echo $coupon->code; ?></h3>
						<h2 style  = "position:absolute; top:35px; margin-top:0; left:30px;font-size:15px; width:175px; text-align:center;">Coupon Code:</h2>
					
					</div>
					<div style="margin-left:180px; height:100px;">
						<strong> Expires: </strong><?php echo  date("F j, Y", $coupon->expires); ?> 
						<br>
						<strong> Coupon Is: </strong><?php if($coupon->public){echo "PUBLIC";}else{echo "PRIVATE";} ?>
						<br>
						<strong> Code: </strong><?php echo $coupon->code; ?>
						<br>
						<button name="deleteCoupon" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-trash" style="margin:%;font-size:15px;"></span> Coupon</button>
							
					</div>
									
				</li>
				
			<?php }	?>
		
		</div>
		
	</div>
</div>
				
		
			
			
			

<div class= "main" style="position:fixed;height:85%;top:0;width:22%;margin-right:33%;margin-top:100px; left:76%">
<h3> Extras: </h3>   
<div id = "mainExtras" style="height:55%;background-color:lightgrey;border-style: dashed;overflow:auto;">
	<ul class= "list-group" id="extraDocket">
	<?php
	foreach($rest->menu->extras as $extra){
		
		echo '<li class="list-group-item" id="extra_'.$extra->id.'" name = "extra_'.$extra->id.'">'.$extra->name.'</li>';
		
	}
	?>
	</ul>
</div>
<div id = "trashExtras"  style="padding:15px;height:20%;background-color:lightgrey;border-style: dashed;">
	<span class="glyphicon glyphicon-trash" style="margin:%;font-size:35px;"></span>
</div>
<button class="btn btn-primary" name = "CreateExtra" data-toggle="modal" data-target="#NewExtra" >Create New Extra</button>
</div>                                                                                        


<div class="modal fade" id="NewExtra" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title" id="myModalLabel">New Extra:</h3>
      </div>
      <div class="modal-body">
        <div class = "form-group col-xs-12">
			
        	<input id = "ExtraId" name="id" class="hidden"></input>
        	<h2> <label for="ExtraName">Reference Tag: </label></h2>
        	<input id ="ExtraName" name="name" class="form-control" type = "text" placeholder = "Extra Name"></input>
			<h2><label for="ExtraQuestion">Question that describes Extra (i.e. "Would you like to make it a combo?"):</label></h2>
			<textarea id = "ExtraQuestion" name="question" class="form-control" type = "text" placeholder = "Question"></textarea>
			<h2> <label for="ExtraType">Does the customer choose one option (or) can they choose many options?<label></h2>
			<span id="ExtraError1" class="label label-warning hidden" style="font-size:14px">**Required**</span>
			<select id= "ExtraType">
				<option name = "default" value = "0">Please select option type: </option>
				<option name = "selectOne" value = "1">Option One: Choose single item from list </option>
				<option name = "selectMany" value = "2"> Option Two: Choose any items or toppings </option>
			</select>
			<br>
			<h2><label> Options: </label></h2>
			<div class="form-group"><label class="checkbox-inline"> Do any of these options require sub options?</label> <input type = "checkbox" id= "ExtraHasExtras" name="ExtraHasExtras" style="margin-left:15px;" onclick="$('.extraHasExtras').toggle()" > Yes/No </input></div> 
			<ul class= "list-group" id="ExtraOptions">
				
				<li id="option_prototype" class = "hidden list-group-item">
					<span>Name: </span><input name = "name" type="text" placeholder = "Your text here."> </input>
					<span>Price: </span><input name = "price" type="text" placeholder = "$0.00"> </input>
					<button name = "delete" type="button" onclick="$(this).parent().remove()"><span class="glyphicon glyphicon-trash"></span></button>
					<div class="extraHasExtras well">  
					<label>This option links to: </label><ul name = "extraExtras">   </ul> <button class="btn btn-primary" name="chooseExtra" style="float:right;">Add Extra</button> <button class="btn btn-primary " name="hideExtra" style="float:right;">Hide Extra</button>
					</div>
				</li>                                                                                                                                                       
			
			
			
			</ul>
			<div id=extra[1]_buttons>
				<button class="btn btn-primary" name = "moreChoice" onclick="expandExtra()" type="button">More Options </button>
				<button class="btn btn-primary" name = "generatePreview" onclick="generatePreview();" type="button">Generate Preview </button>
			</div>
			
		</div>	
	  </div>
      <div class="modal-footer">
      	<div id = "ExtraPreview" style="text-align:left !important;">
      	<h3 >Preview:</h3>
      		
      			<div class="modal-content">
      				<img src ="../images/avatars/avatar1.jpg" /><h3 id="preview_question" class="modal-header">
      				<!--Question -->
      				</h3>
      				<div  class="modal-body">
						<select id="preview_select" class = "hidden">
						
						</select>
						<div id = "preview_check" class = "hidden">
						
						</div>  				
      				</div>
      				<div class="modal-footer">
      				<button class="btn btn-primary" name = "preview_continue" disabled onclick="expandExtra()" type="button">Continue </button>
      				<button class="btn btn-primary" name = "preview_cancel" disabled onclick="expandExtra()" type="button">Cancel</button>
      				</div>
      			</div>	
      		
      
      	
      	
      	</div>
      	<br>
      	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="saveExtra()">Save Extra</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="tutorial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Create Coupons: </h3>
      	<button type="button" class="close" onclick="$('#tutorial').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
      			<h3  style="line-height:25px;font-size:22px;"> 1. Description: </h3>
      			<h2 style="line-height:25px;font-size:20px;"> Coupons allow you to offer a feature item or a discounted price </h2>
      			<h3  style="line-height:25px;font-size:22px;"> 2. Discount Price: </h3>
      			<h2 style="line-height:25px;font-size:20px;"> This option provides a percentage discount off the subtotal of the order</h2>
      			<h3 style="line-height:25px;font-size:22px;"> 3. Feature Item: </h3>
      			<h2  style="line-height:25px;font-size:20px;"> You should familiarize yourself with the Item and Extra components on the edit menu page </h2>
      			<h3 style="line-height:25px;font-size:22px;"> 4. Options: </h3>
      			<h2  style="line-height:25px;font-size:20px;">Public/Private:  A public coupon will be promoted on our website. A private coupon will not be displayed on the website but can be distributed in print or online media.</h2>
      			<h2  style="line-height:25px;font-size:20px;">Expires:  The coupon is only valid until its expiry date.</h2>
      			<h3 style="line-height:25px;font-size:22px;">5. Click Generate Coupon and it is ready for use.  An image of the coupon appears below. </h3>
      				<div class="well">
						<img src="tutorial/coupon.jpg"/>
					</div>
      			
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#tutorial').modal('hide')" type="button">Okay</button>
      </div>    
    </div>
  </div>
</div>

<div id = "prototypes" class = "container hidden">
				
<li id="coupon_prototype" class="list-group-item"  style = "height:130px;">
	<div style="position:relative; width:175px;">
		<a name="prototype_link" style = "position:absolute; top:0; left:0; width:175px; height:100px; z-index:1000;"></a>
		<img style = "position:absolute; top:0; left:0; width:175px; height:100px;" src="/images/biz_card_blank.png"/>
		<h2 name="prototype_title" style = "position:absolute; top:0px;line-height:15px; left:5px;font-size:15px; width:160px; text-align:center;" > </h3>
		<img  style = "position:absolute; top:22px; left:0;height:56px;width:56px;" src="<?php echo $rest->sm_image; ?>"/>
		<h3 name="prototype_code" style  = "position:absolute; top:55px; margin-top:0; left:67px;font-size:15px; width:100px; text-align:center;"></h3>
		<h2 style  = "position:absolute; top:35px; margin-top:0; left:30px;font-size:15px; width:175px; text-align:center;">Coupon Code:</h2>
	
	</div>
	<div style="margin-left:180px; height:100px;">
		<strong> Expires: </strong><span name="prototype_expires"></span> 
		<br>
		<strong> Coupon Is: </strong><span name="prototype_public"></span>
		<br>
		<strong> Code: </strong><span name="prototype_code"></span>
		<br>
		<button class="btn btn-default btn-sm" name="prototype_delete"><span class="glyphicon glyphicon-trash" style="font-size:15px;"></span> Coupon</button>
			
	</div>
					
</li>


	<div id = "rest_extras">
			<table>
			<tr>
			<td>
			<div id = "extra_1">
				<input id = "extra_1_title" type="text" placeholder = "extra_1"></input>
				<select id= "extra_1_typeof">
					<option name = "selectOne" value = "A">Option type: Select One Item from dropdown list </option>
					<option name = "selectMany" value = "B"> Option type: Select Unlimited Items or Toppings </option>
				</select>
				<div id=extra_1_choices>
					<input id = "extra_1_1_name" type="text"> 
					<input id = "extra_1_1_price" type="text"> 
				</div>
				<div id=extra_1_buttons>
					<button name = "moreChoice" type="button">More Options </button>
					<button name = "lessChoice" type="button" disabled = "disabled">Less Options</button>
				</div>
			</div>
			</td>
			</tr>
			</table>
	</div>
    

		

								
	<div id = "extra_prototype">
		<input id = "extra_prototype_title" type="text" class = "row" placeholder = "extra_prototype"></input>
		<select id= "extra_prototype_typeof">
			<option name = "selectOne" value = "A">Option type: Select One Item from dropdown list </option>
			<option name = "selectMany" value = "B"> Option type: Select Unlimited Items or Toppings </option>
		</select>
		<div id=extra_prototype_choices>
			<input id = "extra_prototype_1_name" type="text"> 
			<input id = "extra_prototype_1_price" type="text"> 
		</div>
		<div id=extra_prototype_buttons>
			<button name = "moreChoice" type="button"> 
			<button name = "lessChoice" type="button" disabled = "disabled"> 
		</div>
	</div>
</div>

</body>
</html>

<?php $conn->close(); ?>