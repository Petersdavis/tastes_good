<?php include '../boilerplate.php'; 
if(!isset($_SESSION['pass'])||!$_SESSION['pass']){
	header('Location: ../');
}

checkProduction();?>
<?php include '../dbconnect.php'; ?> 

<?php html_Header("Restaurant Dashboard"); 

$new_rest = getattribute('new_rest');
if($new_rest){
	echo "<script>new_rest=1</script>";
}else{
	echo "<script>new_rest=0</script>";
}


if(isset($_SESSION['rest_id'])){
	$rest_id = $_SESSION['rest_id'];
}else{
	exit("please login");
}

echo '<script> rest_id = "'. $rest_id .'"; </script>'; 
				
$file = __FILE__;
$rest = new restaurant ();
$rest->grabRest($rest_id);
$rest->grabSerial($rest_id); 
include 'header.php';
?>


<!--Icons-->
<script src="js/lumino.glyphs.js"></script>

<!--MyScripts -->

<script src="../scripts/newmenu.js"></script>
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
		<div class="row" style = "background-color:rgba(210,245,225,1);margin-left:5px;margin-right:5px;" >
			<div class="col-xs-12 ">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Menu Builder: <button onclick = "$('#tutorial').modal('show');" class = "btn btn-primary" style="float:right;font-size:20px" >?</button> <button onclick = "$('#VidTutorial').modal('show');" class = "btn btn-primary" style="float:right;font-size:20px" ><span class="glyphicon glyphicon-film" aria-hidden="true" ></span>  Video Tutorial</button>  </h3> 
				<h2 style = "margin-top:0; padding-top:10px; width:100%;font-size:20px;"> 
					<div class = "row" style="background-color:#f0ffff">
						<div class = "col-xs-12">
							<h3> Categories: </h3>
						</div>	
						<div class= "col-xs-4">
							<button type="button" class="btn btn-block btn-default" data-toggle="modal" name="NewCategory" data-target="#NewCategory"><span class="glyphicon glyphicon-plus-sign"></span> New Category:</button>
						</div>
						<div class = "col-xs-4">
							<button type="button" class ="btn btn-block btn-default" name="submit" id = "submitform" onclick="SubmitForm()">Save Menu</button>
						</div>
						<div class = "col-xs-4">
							<a class ="btn btn-block btn-default"  href = "../order.php?rest_id=<?php echo $rest_id; ?>" name="test" id = "test_menu">Test Menu</a>
						</div>
						
					</div>
					
					
					
				</h2>
				
			</div>
			
		</div> <!--/Row -->
		
		
		<ul id = "rest_categories" class = "list-group" style="margin:5px;">
					
								<?php 
								foreach($rest->menu->categories as $category){
													
									echo '<li  id = "category_'.$category->id.'" style = "background-color:#f0ffff; margin-bottom:10px;" class="list-group-item" name="categories">';
										echo '<div class="row" style="padding-right:15px">';
												echo '<div class = "col-xs-8" style="padding-right:25px;"> ';
														echo ' 	<div class = "row">';	
															echo ' 	<div name = "category_'.$category->id.'" class = "form-group col-xs-12">';
															echo '		<h3> Category: <span style="color:#633E26" id="category_'.$category->id.'_label" name="label">'.$category->category. '</span></h3>';
															echo '		<input id = "category_'.$category->id.'_id" name="category_id" class="form-control hidden" type = "text" value = "'.$category->id.'"></input>';
															echo '		<input id = "category_'.$category->id.'_category" name="category" class="form-control hidden" type = "text" value =  "'.$category->category.'" placeholder = "'.$category->category.'"></input>';
															
															echo '		</h3>';
															echo '	</div>';
														echo '	</div>';
														echo ' 	<div class = "row">';	
														echo '		<button type="button" class="btn col-xs-3" name="Change_Cat" id = "Change_Cat_'.$category->id.'"><span class="glyphicon glyphicon-pencil"></span> Name </button>';
														echo '		<button type="button" class="btn col-xs-3" name="Delete_Cat" id = "Delete_Cat_'.$category->id.'"><span class="glyphicon glyphicon-trash"></span> Delete </button>';
														echo '		<button type="button" class="btn col-xs-3" name="Promote_Cat" id = "Promote_Cat_'.$category->id.'"><span class="glyphicon glyphicon-hand-up"></span> Move Up </button>';
														echo '		<button type="button" class="btn col-xs-3"  name="Add_Item" id = "Add_Item_'.$category->id.'" data-toggle="modal"  data-target="#NewItem"><span class="glyphicon glyphicon-plus-sign"></span> Item:</button>';
														echo '	</div>'; 
														echo ' 	<div class = "row">';	
														echo '		<button type="button" class="btn btn-default col-xs-12" name="Reveal_Item"><h2 class = "vcenter">Show/Hide Items</h2></button>';
														echo '	</div>';  
												echo '	</div>';       
												echo '<ul class="list-group col-xs-4 no-padding" name="categoryDocket"  style="min-height:130px;background-color:lightgrey;border-style: dashed;">';
												  if(count($category->extras)==0){
													  echo '<li class="placeholder list-group-item" style="color:darkgrey;"> Drag/Drop Extras to Category </li>'; 
												  }else{
													foreach($category->extras as $extra_id){
													   
														$extra = new Extra();
													  
													  //get the details of the extra
													  foreach($rest->menu->extras as $z){
														  if ($z->id == $extra_id){
															  $extra->name = $z->name;
															 }
													  }
													  
														echo '<li class="list-group-item ui-draggable ui-draggable-handle" name="extra_'.$extra_id.'">'.$extra->name.'</li>';
													}
												  }
												
												
												echo '</ul>';
										echo '</div>';           
										echo '<div class="row" style="padding-right:15px">';		  
											echo '<div class = "col-xs-12" >'; 
												
												echo '<ul id = "category_'.$category->id.'_items" name="Category_Items" class = "list-group""> ';
												foreach($category->items as $item){		
													
													echo '	<li  id ="item_'.$item->id.'"  name = "items" class="list-group-item row">';
														echo ' 	<div  id = "category_'.$category->id.'_item_'.$item->id.'" class = "form-group col-xs-8">';
														echo '	<input id = "category_'.$category->id.'_item_'.$item->id.'_id" name = "item_id" class= "form-control hidden" type = "text" value = "'.$item->id.'"></input>';
														echo '	<input id = "category_'.$category->id.'_item_'.$item->id.'_category" name = "item_category" class= "form-control hidden" type = "text" value = "'.$category->category.'"></input>';
														echo '  <div class = "row">';
														echo '  		<label class = "col-xs-4" for="category_'.$category->id.'_item_'.$item->id.'_product"> Item: </label>';
														echo '			<input class = "col-xs-8" id = "category_'.$category->id.'_item_'.$item->id.'_product" name = "item_product" class= "form-control" type = "text" value = "'.$item->product.'" placeholder = "'.$item->product.'"></input>';
														echo '	</div>';
														echo '  <div class = "row">';
														echo '  		<label class = "col-xs-4" for ="category_'.$category->id.'_item_'.$item->id.'_price"> Price: </label>';
														echo '			<input class = "col-xs-4" id = "category_'.$category->id.'_item_'.$item->id.'_price" name = "item_price" class= "form-control" type = "text" value = "$'.$item->price.'"  placeholder = "$'.$item->price.'"></input>';
														echo '			<button type="button" class= "btn btn-sm col-xs-4" name="DeleteItem"><span class="glyphicon glyphicon-trash"></span>Delete Item</button>';
														echo '	</div>';
														echo '  <div class = "row">';
														echo '			<div class = "col-xs-12">';
														echo '  			<label for="category_'.$category->id.'_item_'.$item->id.'_description"> Description: </label>';
														echo '			</div>';
														echo '			<div class = "col-xs-12">';
														echo '				<textarea class="form-control" style="width:100%;max-width:100%;" name="item_description" id = "category_'.$category->id.'_item_'.$item->id.'_description" type = "text"  placeholder = "'.$item->description.'">'.$item->description.'</textarea>';
														echo '			</div>';
														echo '  </div>';
														echo '  </div>';
														echo '	<ul class="list-group col-xs-4 no-padding" name="itemDocket"  style="min-height:130px;background-color:lightgrey;border-style: dashed;">';
														 if(count($item->extras)==0){
															  echo '<li class="placeholder list-group-item" style="color:darkgrey;"> Drag/Drop Attach Extras to Item:  </li> '; 
														  }else{
															foreach( $item->extras as $extra_id){
															
															  $extra = new Extra();
															  foreach($rest->menu->extras as $z){
																  if ($z->id == $extra_id){
																	  $extra = $z;
																  }
															  }
																
																
																echo '<li class="list-group-item ui-draggable ui-draggable-handle" name="extra_'.$extra->id.'">'.$extra->name.'</li>';
															}
														  }
														 
														
														
														echo '</ul>';
													echo '	</li>';                 
															
															
													
													}
												
												echo '</ul>';	                       
											echo '</div>';                         
										echo '</div>';     
									echo '</li>';		
								
								}
								echo '<script>lastCategory = '.$rest->menu->lastCategory.'; lastItem = '.$rest->menu->lastItem.'; lastExtra = '.$rest->menu->lastExtra.'</script>';
								if(count($rest->menu->extras)>0){
									echo "<script>Extras= ".json_encode($rest->menu->extras).";</script>";
								}else{echo '<script>Extras = [];</script>';}
								
								?>
							
							</ul>
		
	</div><!--/.container-->
</div> 
	


<div class= "main" style="position:fixed;height:85%;top:0;overflow:auto;width:22%;margin-right:33%;margin-top:100px;padding:10px; left:76%">
<h3>Extra Questions: </h3>   
<div id = "mainExtras" style="height:55%;overflow:auto;background-color:lightgrey;border-style: dashed;">
	<ul class= "list-group" id="extraDocket">
	<?php
	
		foreach($rest->menu->extras as $extra){
			
			echo '<li class="list-group-item" id="extra_'.$extra->id.'" name = "extra_'.$extra->id.'">'.$extra->name.'</li>';
			
		}
	
	?>
	</ul>
</div>
<div id = "trashExtras"  style="padding:15px;min-height:20%;background-color:lightgrey;border-style: dashed;">
	<span class="glyphicon glyphicon-trash" style="margin:%;font-size:35px;"></span> Remove/Delete Extras Here.
</div>
<button class="btn btn-primary btn-block" name = "CreateExtra" data-toggle="modal" data-target="#NewExtra" style="margin-top:10px;" >Create New Extra</button>
</div>                                                                                        

	
<div class="modal fade" id="NewCategory" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title" id="myModalLabel">New Category:</h3>
      </div>
      <div class="modal-body">
        <div  class = "form-group col-xs-12">
			
			<input id = "NewCategoryName" name="category" class="form-control" type = "text" placeholder = "Your Category Name"></input>
			
		</div>	
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="NewCategory($('#NewCategoryName').val())" data-dismiss="modal">Save Category</button>
        <button type="button" class="btn btn-primary" onclick="NewCategory($('#NewCategoryName').val());setTimeout(function(){initializeAddCategory();$('#NewCategory').modal('show')}, 600)" data-dismiss="modal">Save & ++ Category</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="tutorial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" style="width:90%" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Menu Builder: </h3>
      	<button type="button" class="close" onclick="$('#tutorial').modal('hide');" style="position: fixed;top: 15px;right: 40px; font-size: 35px; background-color: rgb(210,245,225);" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
					<h3  style="line-height:25px;font-size:22px;"> 1. Description: </h3>
					<h2 style="line-height:25px;font-size:20px;"> This page gives you complete control over your menu.  Just always remember to save your work!</h2>
					<h3 style="line-height:25px;font-size:22px;"> 2. Overview: </h3>
					<h2  style="line-height:25px;font-size:20px;"> The menu is built on three components: </h2>
					<ul> 
						<li  style="line-height:25px;font-size:20px;">Categories</li>
						<li  style="line-height:25px;font-size:20px;">Items</li>
						<li   style="line-height:25px;font-size:20px;">Extras </li>
					</ul>
					<h3 style="line-height:25px;font-size:22px;"> 2. Categories: </h3>
					<h2  style="line-height:25px;font-size:20px;">Create new categories by clicking  <img src="tutorial/new_category.jpg" style = "display:inline-block"/></h2>
					<h2  style="line-height:25px;font-size:20px;">Change a  category's name by clicking  <img src="tutorial/edit_category.jpg" style = "display:inline-block"/></h2>
					<h2  style="line-height:25px;font-size:20px;">Move a category up by clicking  <img src="tutorial/promote_category.jpg" style = "display:inline-block"/></h2>
					<h2  style="line-height:25px;font-size:20px;">Delete a category by clicking <img src="tutorial/delete_category.jpg" style = "display:inline-block"/></h2>
					<h2  style="line-height:25px;font-size:20px;">Warning!! If you delete a category you delete all of the categories items.</h2>
					
					<h3 style="line-height:25px;font-size:22px;"> 3. Items: </h3>
					<h2  style="line-height:25px;font-size:20px;">Add a new items to an existing category by clicking  <img src="tutorial/new_item.jpg" style = "display:inline-block"/></h2>
					<h2  style="line-height:25px;font-size:20px;">Edit an item by clicking on the items: name, price, or description. </h2>
					<h2  style="line-height:25px;font-size:20px;">Delete an item by clicking <img src="tutorial/delete_item.jpg" style = "display:inline-block"/></h2>
					
					
					<h3 style="line-height:25px;font-size:22px;"> 3. Extras: </h3>
					<h2  style="line-height:25px;font-size:20px;">Extras are the backbone of your menu. They allow your customers to upsize and modify menu items.</h2>
					<br>
					<h2  style="line-height:25px;font-size:20px;">Lets say your menu includes an item called the "Double Bacon Cheese Burger":  </h2>
					<div class="well">
						<img src="tutorial/dbl_bac.jpg"/>
					</div>
					<h2  style="line-height:25px;font-size:20px;">This item can be served by itself or as a combo with a side and a drink.  The first extra on this item will be called "Combo".  If the customer adds the "Combo"  he can then choose a "Side" and a "Drink" </h2> 
					<h2  style="line-height:25px;font-size:20px;">First of all we will need to create each of these three Extras.</h2>
					<h2  style="line-height:25px;font-size:20px;">We do so by clicking <img src="tutorial/new_extra.jpg" style = "display:inline-block"/></h2>
					<h2  style="line-height:25px;font-size:20px;">Which brings up the Extra Dialogue Box:</h2>
					<div class="well">
						<img src="tutorial/extra.jpg"/>
					</div>
					
					<h2  style="line-height:25px;font-size:20px;">After filling out the details of the extra.  We pick Option 1--because we want the customer to make a single selection from the available options. </h2>
					<h2  style="line-height:25px;font-size:20px;">We include an option named "Combo" that costs "$3.59", and a second option named "No Combo" that costs nothing.</h2>
					<h2  style="line-height:25px;font-size:20px;">Then scrolling to the bottom we click  <img src="tutorial/save_extra.jpg" style = "display:inline-block"/></h2>
					<h2  style="line-height:25px;font-size:20px;">The extra is then added to the Extras pane at the right of the screen.  You can edit an extra by clicking on it in this pane.</h2>
					<h2  style="line-height:25px;font-size:20px;">Now we want to add the Extra to the Item.  We do that by dragging the Extra from the Extras pane to the docket on the item. </h2>
					<div class="well">
						<img src="tutorial/drag_extra.jpg"/>
					</div>
					<h2  style="line-height:25px;font-size:20px;">You can remove an extra from a Category or an Item by Dragging it to the Extra Trash at the bottom of the Extra pane.  You can permenantly delete an Extra by dragging it from the Extra pane to the Extra Trash</h2>
					<h2  style="line-height:25px;font-size:20px;">Now we create two more extras named "Side" and "Drink"</h2>
					<div class="well">
						<img src="tutorial/more_extras.jpg"/>
					</div>
					<h2  style="line-height:25px;font-size:20px;"> Using the <img src="tutorial/preview_extra.jpg" style = "display:inline-block"/> you can immediatly see how the extra will appear to the customer: </h2>
					<div class="well">
						<img src="tutorial/preview.jpg"/>
					</div>
					<h2  style="line-height:25px;font-size:20px;">Now that we have created our three extras there is just one final step. </h2>
					<h2  style="line-height:25px;font-size:20px;">Clicking on the "Combo" Extra we open it for editing</h2>
					<div class="well">
						<img src="tutorial/edit_combo.jpg"/>
					</div>
					<h2  style="line-height:25px;font-size:20px;">We click the check box to enable adding extras to the Extra </h2>
					<div class="well">
						<img src="tutorial/extra_extra.jpg"/>
					</div>
					<h2  style="line-height:25px;font-size:20px;">We click add extra to the "Combo" Option and drag over the two other Extras, "Side" and "Drink" </h2>
					<div class="well">
						<img src="tutorial/extra_finish.jpg"/>
					</div>
					<h2  style="line-height:25px;font-size:20px;">Were done!  Now when a customer orders a Double Bacon Ch. Burger, they will be asked if they want a combo.  If they do want a combo they will be asked for a choice of sides and drinks. </h2>
					<h2  style="line-height:25px;font-size:20px;">To test this out we click <img src="tutorial/test.jpg" style = "display:inline-block"/> We build an order and submit it (You are free to submit test orders until your restaurant is active).</h2>
					<h2  style="line-height:25px;font-size:20px;">When I submit the test-order a receipt should print in my restaurant and I should receive an email receipt as well.  The receipt will look something like this: </h2>
					<div class="well">
						<img src="tutorial/receipt.jpg"/>
					</div>
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#tutorial').modal('hide')" type="button">Okay</button>
      </div>    
    </div>
  </div>
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
        	<h2> <label for="ExtraName">Reference Tag (i.e. "Combo"): </label></h2>
        	<input id ="ExtraName" name="name" class="form-control" type = "text" placeholder = "Extra Name"></input>
			<h2><label for="ExtraQuestion">Question that describes Extra (i.e. "Would you like to make it a combo?"):</label></h2>
			<textarea id = "ExtraQuestion" name="question" class="form-control" type = "text" placeholder = "Question"></textarea>
			<h2> <label> Should the customer be able to choose more than one option? <label></h2>
			<input type="text" id="ExtraType" style="display:none;" value="1"/>
			<div class = "row">
			<div class = "col-xs-6">
			<button class="btn btn-primary btn-block" id="typeOne">Choose One Option</button>
			</div>
			<div class = "col-xs-6">
			<button class="btn btn-primary btn-block" id="typeTwo">Choose Any Options</button>
			</div>
			</div>
			
			<span id="ExtraError1" class="label label-warning hidden" style="font-size:14px">**Required**</span>
			<br>
			<h2><label> Options: </label></h2>
			<ul class= "list-group" id="ExtraOptions">
				
				<li id="option_prototype" class = "hidden list-group-item">
					<span>Name: </span><input name = "name" type="text" placeholder = "Your text here."> </input>
					<span>Price: </span><input name = "price" type="text" value= "$0.00" placeholder = "$0.00"> </input>
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
						<div id="preview_select" hidden>
						
						</div>
						<div id = "preview_check" hidden>
						
						</div>  				
      				</div>
      				<div class="modal-footer">
      				<button class="btn btn-primary" id = "preview_continue" disabled  type="button">Continue </button>
      				<button class="btn btn-primary" id = "preview_cancel" disabled  type="button">Cancel</button>
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


<div class="modal fade" id="NewItem" data-category = "" data-categoryid = "" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title">Category: <span id="newItem_category" style="color:rgba(30,30,70,1);"></span><span id="newItem_category_id" class="hidden"></span></h3>
      </div>
      <div class="modal-body">
        
		<form class="form-inline">
			<div class = "form-group" style="width:100%;">
				<label> Item: </label>
				<input id = "newItem_product" name = "product" class= "form-control" type = "text" placeholder = "Item Name"></input>
				<label> Price: </label>
				<input id = "newItem_price" name = "price" class= "form-control" type = "text" value="$0.00" placeholder = "$0.00"></input>
				<div>
					<textarea class="form-control" style="width:100%; max-width:100%;" name="description" id = "newItem_description" type = "text" placeholder = "Your Item described here."></textarea>
				</div>
			</div>	
		</form>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="NewItem($('#newItem_category_id').text(), $('#newItem_category').text(), $('#newItem_product').val(),$('#newItem_price').val(),$('#newItem_description').val() )" data-dismiss="modal">Save Item</button>
          <button type="button" class="btn btn-primary" onclick="NewItem($('#newItem_category_id').text(), $('#newItem_category').text(), $('#newItem_product').val(),$('#newItem_price').val(),$('#newItem_description').val());setTimeout(function(){initializeAddItem();$('#NewItem').modal('show')}, 600) " data-dismiss="modal">Save Item & ++Item</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="VidTutorial" data-category = "" data-categoryid = "" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document" style = "margin:auto;margin-top:25px;width:100%;max-width:1400px;">
    <div class="modal-content" style = "margin:auto;width:1350px;">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true" style="font-size:25px;">&times;</span></button>
        <h3 class="modal-title" style="font-size:25px;">Menu Building Tutorial: </h3>
      </div>
      <div class="modal-body">
         <iframe width="1280" height="720" id="tutorial_vid" src="https://www.youtube.com/embed/7HQY4mVwurg?rel=0" frameborder="0" allowfullscreen></iframe>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"  style="font-size:25px;">Close</button>
      </div>
    </div>
  </div>
</div>

<div id = "prototypes" class = "container hidden">
	<li class="placeholder list-group-item" id = "extra_list_placeholder" style="color:darkgrey;"><strong>Attach Extras To Items or Categories:</strong>
		<ol> 
			<li>Create an Extra Question by clicking "Create New Extra"</li>
			<li>Drag the Extra to the Categories and/or Items that require this Extra</li>
			<li>Chain Extras by attaching an Extra to an Option within an Extra. </li>
		</ol>
	</li>
	<li class="placeholder list-group-item" id ="cat_list_placeholder" style="color:darkgrey;"> Drag/Drop Attach Extras to Category:  </li>
	<li class="placeholder list-group-item" id ="itm_list_placeholder" style="color:darkgrey;"> Drag/Drop Attach Extras to Item:  </li>
    

	<li id = "category_prototype"  style = "background-color:#f0ffff; margin-bottom:10px;" class="list-group-item" name="categories">
		<div class="row" style="padding-right:15px">
			<div class = "col-xs-8">
				<div class = "row">
					<div id= "category_prototype" class = "form-group col-xs-12">
						<h3> Category: <span style="color:#633E26" id="category_prototype_label" name="label"> </span></h3>
						<input id = "category_prototype_id" name="category_id" class="form-control hidden" type = "text" ></input>
						<input id = "category_prototype_category" name="category" class="form-control hidden" type = "text"></input>
					</div>
				</div>
				<div class = "row">	
					<button type="button" class="btn col-xs-3" name="Change_Cat" id = "Change_Cat_prototype" onclick = "ChangeCategory('.$category->id.')"><span class="glyphicon glyphicon-pencil"></span> Name </button>
					<button type="button" class="btn col-xs-3" name="Delete_Cat" id = "Delete_Cat_prototype" onclick = "DeleteCategory()"><span class="glyphicon glyphicon-trash"></span>Delete </button>
					<button type="button" class="btn col-xs-3" name="Promote_Cat" id = "Promote_Cat_prototype" onclick = "PromoteCategory()" ><span class="glyphicon glyphicon-hand-up"></span> Promote</button>
					<button type="button" class="btn col-xs-3"  name="Add_Item" id = "Add_Item_prototype"  data-toggle="modal" data-target="#NewItem"><span class="glyphicon glyphicon-plus-sign"></span> Add Item:</button>
				</div>
				<div class = "row">		
					<button type="button" class="col-xs-12" name="Reveal_Item" id = "Reveal_Item_prototype"><h2 class = "vcenter">Show/Hide Items</h2></button>
				</div>
			</div>
			<ul class="list-group col-xs-4 no-padding" name="categoryDocket"  style="min-height:130px;background-color:lightgrey;border-style: dashed;"><li class="placeholder list-group-item" style="color:darkgrey;">  Drag/Drop Extras to Category </li></ul>
		</div>
		<div class="row" style="padding-right:15px">
				<div  class = "well col-xs-12" > 
					<ul id="category_prototype_items" name="Category_Items" class = "list-group" > 
					</ul>	
				</div>
		</div>
	</li>		
		

	<li id="item_prototype" class="list-group-item row" name="items">
		<div  id = "category_prototope_item_prototype" class = "form-group col-xs-8">
			<input id = "category_prototope_item_prototype_id" name = "item_id" class= "form-control hidden" type = "text" value = "prototype"></input>
			<input id = "category_prototope_item_prototype_category" name = "item_category" class= "form-control hidden" type = "text" value = "'.$category->category.'"></input>
			<div class = "row">
				<label class = "col-xs-4" for="category_prototope_item_prototype_product"> Item: </label>
				<input class = "col-xs-8" id = "category_prototope_item_prototype_product" name = "item_product" class= "form-control" type = "text" placeholder = "Item Name"></input>
			</div>
			<div class = "row">
				<label class = "col-xs-4" for ="category_prototope_item_prototype_price"> Price: </label>
				<input class = "col-xs-4" id = "category_prototope_item_prototype_price" name = "item_price" class= "form-control" type = "text" value = "$0.00" placeholder = "$0.00"></input>
				<button type="button" class= "btn btn-sm col-xs-4" name="DeleteItem"><span class="glyphicon glyphicon-trash"></span>Delete Item</button>
			</div>
			<div class = "row">
				<div class = "col-xs-12">
					<label for="category_prototope_item_prototype_description"> Description: </label>
				</div>
				<div class = "col-xs-12">
					<textarea class="form-control" style="width:100%;max-width:100%;" name="item_description" id = "category_prototope_item_prototype_description" type = "text" placeholder = "Describe the item here.">Describe the item here.</textarea>
				</div>
			</div>
		</div>	
		<ul class="list-group col-xs-4 no-padding" name="itemDocket"  style="min-height:130px;background-color:lightgrey;border-style: dashed;"><li class="placeholder list-group-item" style="color:darkgrey;">  Drag/Drop Extras to Category  </li></ul>
	</li>

								
	
</div>

</body>
</html>

<?php  $conn->close(); ?>