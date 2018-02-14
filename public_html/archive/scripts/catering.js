//Global Variable Initializations


//Site Specific Functions\



function initializeAddCategory(){
	$('#NewCategoryName').val("");
}

function initializeAddItem(){
	$('#newItem_product').val("");
	$('#newItem_price').val("");
	$('#newItem_description').val("");
}

function initializeAddExtra(){
	$('#ExtraError1').addClass('hidden');
	$('#ExtraId').val("new");
	$('#ExtraName').val("");
	$('#ExtraQuestion').val("");
	$('#ExtraError1').hide();
	$('#ExtraType').val(0);
	$('#ExtraHasExtras').prop('checked', false);
	$('.extraHasExtras').hide()
	$('#ExtraOptions').find('li').not('#option_prototype').remove();
	expandExtra();
}



function AddCategory(){
	newDiv = $('#category_prototype').clone();  
	newDiv.appendTo('#rest_categories');
	
	return newDiv;
	
}


function NewCategory(Category){
	window.lastCategory = window.lastCategory + 1
		
	var new_cat = AddCategory()
	
	var new_catElements = $(new_cat).find('*');
	
	for(var i=0;i<new_catElements.length;++i){
		if ($(new_catElements[i]).attr("id")){
		$(new_catElements[i]).attr("id", $(new_catElements[i]).attr('id').replace("prototype", window.lastCategory));
		}
	}
	
	$(new_cat).attr("id", $(new_cat).attr("id").replace("prototype", window.lastCategory));
	
	$(new_cat).find('[name="label"]').text(Category);
	$(new_cat).find('[name="category"]').val(Category);
	$(new_cat).find('[name="hierarchy"]').val(window.lastCategory);
	
	$(new_cat).find('[name="Change_Cat"]').click(function(){
		var cat_name = prompt("Please the category name", $(this).parent().parent().find('[name="label"]').text());
		if(cat_name){
			$(this).parent().parent().find('[name="label"]').text(cat_name);
			$(this).parent().parent().find('[name="category"]').val(cat_name);
		}
	})
	
	$(new_cat).find('[name="Promote_Cat"]').click(function(){
		var cat = upTo(this, "li");
		if($(cat).prev().length>0){
			$(cat).insertBefore($(cat).prev());
		}
	});
	
	$(new_cat).find('[name="Add_Item"]').click(function(){
		initializeAddItem();
		var cat = upTo(this, "li");
		var cat_id = $(cat).find('[name=hierarchy]').val();
		var category = $(cat).find('[name=category]').val();
		$('#newItem_category').text(category);
		$('#newItem_category_id').text(cat_id);
			
	});
	
	$(new_cat).find('[name=Delete_Cat]').click(function(){
			var cat = upTo(this, "li");
			$(cat).hide();
			$(cat).find('[name=category_id]').val("cancel")
	});		
	
	$(new_cat).find('[name=Category_Items]').hide();
	
	$(new_cat).find('[name=Reveal_Item]').click(function(){
			$(this).toggleClass("active");  //css formating
			var cat = upTo(this, "li")
			$(cat).find('[name=Category_Items]').toggle();	
		});
	
	$(new_cat).find('[name="categoryDocket"]').droppable({ activeClass: "ui-state-default",
				  hoverClass: "ui-state-hover",
				  accept: ":not(.ui-sortable-helper)",
				  drop: function( event, ui ) {
					 $( this ).find( ".placeholder" ).remove();
					 $( "<li class='list-group-item'></li>" ).text( ui.draggable.text() ).attr("name", ui.draggable.attr("name")).appendTo( this ).draggable({
						  appendTo: "body",
						  helper: "clone"
			  		 });
				  }
			}).sortable({
			  items: "li:not(.placeholder)",
			  sort: function() {
				// gets added unintentionally by droppable interacting with sortable
				// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
				$( this ).removeClass( "ui-state-default" );
			}});
	
	PlaceHolder();
	
}

function expandExtra(){
	var newOption = $('#option_prototype').clone(true);
	$(newOption).attr("name", "option");
	var Option_Id = ($('#ExtraOptions').find('li').length-1);
	$(newOption).attr("id", "option_"+Option_Id);
	$(newOption).removeClass("hidden");
	newOption.appendTo('#ExtraOptions');
	return newOption;
	
}

function saveExtra(){
		//form validation
		
		$('#ExtraError1').addClass('hidden');
		$('#ExtraError1').hide()
		
		
		//check the type
		var Type = $('#ExtraType').val();
		if(Type==0){
		    $('#ExtraError1').removeClass('hidden');
		    $('#ExtraError1').show();
			return;
		}
		$('#NewExtra').modal('hide');
		
	
		
		
		if($('#ExtraId').val() == "new"){
		//id  == new
		window.lastExtra=window.lastExtra+1;
		var extraId = window.lastExtra;
		var extraName = $('#ExtraName').val();
		var extraQuestion = $('#ExtraQuestion').val();
		var extraType =$('#ExtraType').val()
		var extraOptions = []
		
		$('#ExtraOptions').find('[name="option"]').each(function(){
			var optionExtra = []	
				$(this).find('[name="extraExtras"]').find('li').each(function(){
						var extraId = $(this).attr("name")
						extraId=extraId.match(/\d+/g)[0];
						optionExtra.push(extraId)
				})		
						
			var option = {'name':$(this).find('[name="name"]').val(), 'price':$(this).find('[name="price"]').val(), 'extras':optionExtra};
			extraOptions.push(option);
		})
		
		
		var extra = {'id':extraId, 'name':extraName, 'question':extraQuestion, 'type':extraType, 'options':extraOptions}
		window.Extras.push(extra);
		
	
	
		//update extras list
		var element = $('<li class="list-group-item"></li>').text(extraName);
		$(element).attr('id','extra_'+extraId)
		$(element).attr('name','extra_'+extraId)
		$('#extraDocket').append($(element));
		$(element).click(function(){
			var extraId = $(this).attr("id")
			extraId=extraId.match(/\d+/g);
			
			theExtra = $.grep( window.Extras, function(e) {
					return e.id == extraId;
			})[0];
			
			var extraName=theExtra.name;
			var extraQuestion = theExtra.question;
			var extraType = theExtra.type;
			var extraOptions = theExtra.options;
			
			$('#ExtraId').val(extraId);
			$('#ExtraName').val(extraName);
			$('#ExtraQuestion').val(extraQuestion);
			$('#ExtraError1').hide();
			$('#ExtraType').val(extraType);
			$('#ExtraHasExtras').prop('checked', false);
			$('.extraHasExtras').hide()
			$('#ExtraOptions').find('li').not('#option_prototype').remove();
			
			var i;
			for(i=0;i<extraOptions.length;++i){
				var newOption = expandExtra();
				$(newOption).find('[name="name"]').val(extraOptions[i].name);
				$(newOption).find('[name="price"]').val(extraOptions[i].price);
				
				$.each(extraOptions[i].extras, function(key, value){
						$('.extraHasExtras').show()
						var optionExtraId= value;
						var extraList = $(newOption).find('[name="extraExtras"]')
						
						var optionExtra =$.grep( window.Extras, function(e) {
								return e.id == optionExtraId;
						})[0];
						
						
						$( '<li class="list-group-item"></li>' ).text(optionExtra.name).attr("name", "extra_"+optionExtraId).appendTo(extraList).draggable({
							 appendTo: "body",
							 helper: "clone"	
						})
						
				})
				//add in the extras
			}
			$('#NewExtra').modal('show');
			
		});
	} else {
		var extraId=$('#ExtraId').val()	
	//update the name in the menu. 
	$('[name="extra_'+extraId+'"]').text($('#ExtraName').val());
	
	//update the json variable
		var i;
		var jsonKey;
		for(i=0;i<window.Extras.length;++i){
			if(window.Extras[i].id == extraId){
				jsonKey = i;
			}
		}
		
		var extraName = $('#ExtraName').val();
		var extraQuestion = $('#ExtraQuestion').val();
		var extraType =$('#ExtraType').val()
		var extraOptions = []
		
		$('#ExtraOptions').find('[name="option"]').each(function(){
			var optionExtra = []	
				$(this).find('[name="extraExtras"]').find('li').each(function(){
						var extraId = $(this).attr("name")
						extraId=extraId.match(/\d+/g)[0];
						optionExtra.push(extraId)
				})		
						
			var option = {'name':$(this).find('[name="name"]').val(), 'price':$(this).find('[name="price"]').val(), 'extras':optionExtra};
			extraOptions.push(option);
		})
		
		
		window.Extras[jsonKey] = {'id':extraId, 'name':extraName, 'question':extraQuestion, 'type':extraType, 'options':extraOptions}
	
	}
		
	
	
	
	 $('#extra_'+extraId).draggable({
      appendTo: "body",
      helper: "clone"
    });
}





function generatePreview(){
	//initialize
	$('#ExtraError1').addClass('hidden');
	$('#preview_select').empty().addClass('hidden');
	$('#preview_check').empty().addClass('hidden');
	
	//set the question:
	$('#preview_question').text( $('#ExtraQuestion').val() );
	
	var Type = $('#ExtraType').val();
	
	switch (Type){
		case "0":
			$('#ExtraError2').removeClass('hidden');
			$('#ExtraError2').show();
			break;
		case "1":
			
			// dropdown
			$('#preview_select').removeClass('hidden');
			$('#NewExtra').find('[name="option"]').each(function( index, element ){
					$('#preview_select').append('<option>'+ $(this).find('[name="name"]').val() +' <strong>'+$(this).find('[name="price"]').val()  +'</strong></option>')
				
			})
			
			
			break;
		case "2":
			//options
			$('#preview_check').removeClass('hidden');
			$('#NewExtra').find('[name="option"]').each(function( index, element ){
					$('#preview_check').append('<label class="checkbox-inline"><input type="checkbox">'+ $(this).find('[name="name"]').val() +' <strong>'+$(this).find('[name="price"]').val()  +'</strong></input></label>')
				
			})
			
			
			break;
	}
	
}



function NewItem(Category_Id, Category, Product, Price, Description){
	window.lastItem = window.lastItem + 1;
	var cat = $('#category_'+Category_Id) //need to adjust for category numbering starting at 0
	
	new_item =  AddItem(cat);
	
	var new_itemElements = $(new_item).find('*');
	
	for(var i=0;i<new_itemElements.length;++i){
		if ($(new_itemElements[i]).attr("id")){
		$(new_itemElements[i]).attr("id", $(new_itemElements[i]).attr('id').replace("prototope", Category_Id));	
		$(new_itemElements[i]).attr("id", $(new_itemElements[i]).attr('id').replace("prototype", window.lastItem));
		}
	}
	
	$(new_item).attr("id", $(new_item).attr("id").replace("prototype", window.lastItem ));
	
	
	$(new_item).find('[name="item_product"]').val(Product);
	$(new_item).find('[name="item_price"]').val(Price).attr("placeholder", Price);
	$(new_item).find('[name="item_category"]').val(Category);
	$(new_item).find('[name="item_description"]').val(Description);
	
	$(new_item).find('[name="itemDocket"]').droppable({ activeClass: "ui-state-default",
				  hoverClass: "ui-state-hover",
				  accept: ":not(.ui-sortable-helper)",
				  drop: function( event, ui ) {
					 $( this ).find( ".placeholder" ).remove();
					 $( "<li class = 'list-group-item'></li>" ).text( ui.draggable.text()).attr("name", ui.draggable.attr("name")).appendTo( this ).draggable({
						  appendTo: "body",
						  helper: "clone"
			  		 });
				  }
			}).sortable({
			  items: "li:not(.placeholder)",
			  sort: function() {
				// gets added unintentionally by droppable interacting with sortable
				// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
				$( this ).removeClass( "ui-state-default" );
			}});
	
	$(new_item).find('[name="DeleteItem"]').click(function(){
		var item = upTo(this, 'li');
		
		$(item).hide();
		$(item).find('[name=item_id]').val("cancel")
	})
			
	
	
	//RevealItems();
	$(cat).find('[name="Category_Items"]').show();
		
}
function RefreshCategories(){

}	

function AddItem(category){
	var div= $(category).find('[name="Category_Items"]');
	newDiv = $('#item_prototype').clone();  
	$(newDiv).appendTo($(div));
	return newDiv;
}

function DeleteCategory(){
	
	var div = upTo(event.target, "li");
	$(div).remove()
	
}

	
 function PromoteCategory(category){
	//just move the li.
}

function SubmitForm(){	
		
		
		//categories JSON
		var categories = []
		var category = {}
		var itm = {}
		var catCollection=document.getElementsByName("categories");
		
		$.each($('[name="categories"]').not('#category_prototype'), function(key, item){
				var catId = $(this).find('[name="category_id"]').val();
				var catCategory = $(this).find('[name="category"]').val();
				var catHierarchy = key;
				var catExtras = [];
				var catItems = [];
				
				$.each($(this).find('[name="categoryDocket"]').find('li').not('.placeholder'), function(key2, item2){
					var extraId = $(this).attr("name")
					extraId=extraId.match(/\d+/g);
					if(extraId.length > 0){
					catExtras.push(extraId[0]);
					}
				})
				
				$.each($(this).find('[name="items"]'), function (key2, item2){
					var itmProduct = $(this).find('[name="item_product"]').val();
					var itmPrice = $(this).find('[name="item_price"]').val();
					var itmDescription = $(this).find('[name="item_description"]').val();
					var itmCategory = catCategory;
					var itmId = $(this).find('[name="item_id"]').val();
					var itmExtras= [];
					
					$.each($(this).find('[name="itemDocket"]').find('li').not('.placeholder'), function (key3, item3){
						var extraId = $(this).attr("name")
						extraId=extraId.match(/\d+/g);
						if(extraId.length > 0){
						   itmExtras.push(extraId[0]);
						}
					})
				
				itm = {'id':itmId, 'product':itmProduct, 'price':itmPrice, 'description':itmDescription, 'extras':itmExtras};
				
				catItems.push(itm);
				
				})
				
				category = {'id':catId, 'category':catCategory, 'hierarchy':catHierarchy, 'items':catItems, 'extras':catExtras};
				categories.push(category);		
		})
		
			
		//console.log(categories);
		var category_json = JSON.stringify(categories)
		var extras_json = JSON.stringify(window.Extras)
		
		
		
		var formData = new FormData ();
					
		formData.append ('categories', category_json);
		formData.append ('extras', extras_json);
		formData.append ('form_type', "menu");
		formData.append ('rest_id', window.rest_id);
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
		    console.log(xhr.response);
		   
		    var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
			checklist.menu = 1;
			localStorage.setItem("TGchecklist", JSON.stringify(checklist));
			//get global variables
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'http://'+ location.hostname + '/uploader.php', true);
		xhr.send(formData);
		
		//Action to the Different Forms
		
	  
}
	
	
	
function validatePrice(value, placeholder){
					
			if(/[\d]/.test(value)){
				var goodvalue = "$"
				goodvalue = goodvalue + /[\d]+[.]{0,1}[\d]{0,2}/.exec(value)[0];
				
				if(!/[.]/.test(goodvalue)){
					goodvalue=goodvalue + "."	
					if(!/[.][\d]{2}/.test(goodvalue)){
						goodvalue=goodvalue + "0"
						if(!/[.][\d]{2}/.test(goodvalue)){
							goodvalue=goodvalue + "0"	
							
						}
					}
				}
				return goodvalue
			} else {return placeholder;}	
}


//stop forms from changing page
document.addEventListener("DOMContentLoaded", function(event) { 
		$('[name=item_price], [name=price]').change(function(){
				
				//or go discard change
		});	
		
		$('[name="Promote_Cat"]').click(function(){
			var cat = upTo(this, "li");
			if($(cat).prev().length>0){
				$(cat).insertBefore($(cat).prev());
			}
		});
				
				
		
		$('[name="Change_Cat"]').click(function(){
				var cat_name = prompt("Please the category name", $(this).parent().parent().find('[name="label"]').text());
				if(cat_name){
					$(this).parent().parent().find('[name="label"]').text(cat_name);
					$(this).parent().parent().find('[name="category"]').val(cat_name);
				}
		});
			
			
		$('[name=NewCategory]').click(function(){
				initializeAddCategory();
		});
		
		$('[name=CreateExtra]').click(function(){
				initializeAddExtra();
		});
		
		onclick="initializeAddExtra(); ;"
		
		$('[name=Category_Items]').hide();
		
		$('[name=Delete_Cat]').click(function(){
			var cat = upTo(this, "li");
			$(cat).remove();
			
		});		
		
		$('[name="Add_Item"]').click(function(){
			initializeAddItem();
			var cat = upTo(this, "li");
			var cat_id = $(cat).find('[name=category_id]').val();
			var category = $(cat).find('[name=category]').val();
			$('#newItem_category').text(category);
			$('#newItem_category_id').text(cat_id);
			
		});
		
		$('[name=Reveal_Item]').click(function(){
			$(this).toggleClass("active");  //css formating
			$(this).parent().parent().parent().parent().find('[name=Category_Items]').toggle();	
		});
		
		$('[name="DeleteItem"]').click(function(){
			var itm = upTo(this, "li");
			$(itm).remove();
			
		})
		
	$('[name="chooseExtra"]').click(function(){
			var element = $('#mainExtras').clone();
			$(element).find('li').attr('class', 'list-group-item');
			$(this).parent().droppable({ activeClass: "ui-state-default",
				  hoverClass: "ui-state-hover",
				  accept: ":not(.ui-sortable-helper)",
				  drop: function( event, ui ) {
					var extraList = $(this).find('ul');
					$( '<li class="list-group-item"></li>' ).text( ui.draggable.text()).appendTo( extraList ).attr("name",ui.draggable.attr("name")).draggable({
						  appendTo: "body",
						  helper: "clone"
			  		 });
				  }
			}).sortable({
			  items: "li:not(.placeholder)",
			  sort: function() {
				// gets added unintentionally by droppable interacting with sortable
				// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
				$( this ).removeClass( "ui-state-default" );
			}});
						
					
			
			$(element).find('li').draggable({
					appendTo: $('#NewExtra'),
					helper: "clone"
			});
			
			$(this).parent().parent().append(element);
			$(this).hide()
			$(this).parent().find('[name="hideExtra"]').show()
			//$(this).prev().append(element);
	})
	
	$('[name="hideExtra"]').click(function(){
			$(this).parent().next().remove();
			$(this).hide()
			$(this).parent().find('[name="chooseExtra"]').show()
	})		
		
	$('[name="hideExtra"]').hide()

	
	//set up the dockets and the extras
    $( "#extraDocket li" ).draggable({
      appendTo: "body",
      helper: "clone"
    });
    
     $( "[name='categoryDocket']").find("li").not(".placeholder").draggable({
      appendTo: "body",
      helper: "clone"
    });
     
     $( "[name='itemDocket']").find("li").not(".placeholder").draggable({
      appendTo: "body",
      helper: "clone"
    });
     
    
    $( "#extraDocket").droppable({
      activeClass: "ui-state-default",
      hoverClass: "ui-state-hover",
      accept: ":not(.ui-sortable-helper)",
      drop: function( event, ui ) {
      if($(ui.draggable).attr("name")!==$(ui.draggable).attr("id")){
				
				$(ui.draggable).remove();
			}
      }
    });
    
    
    
    $( "[name=categoryDocket" ).droppable({
      activeClass: "ui-state-default",
      hoverClass: "ui-state-hover",
      accept: ":not(.ui-sortable-helper)",
      drop: function( event, ui ) {
        $( this ).find( ".placeholder" ).remove();
        $( "<li class='list-group-item'></li>" ).text( ui.draggable.text()).attr("name", ui.draggable.attr("name")).appendTo( this ).draggable({
						  appendTo: "body",
						  helper: "clone"
			  		 });
      }
    }).sortable({
      items: "li:not(.placeholder)",
      sort: function() {
        // gets added unintentionally by droppable interacting with sortable
        // using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
        $( this ).removeClass( "ui-state-default" );
    }});
    
    
     $( "[name=itemDocket]" ).droppable({
      activeClass: "ui-state-default",
      hoverClass: "ui-state-hover",
      accept: ":not(.ui-sortable-helper)",
      drop: function( event, ui ) {
        $( this ).find( ".placeholder" ).remove();
        $( "<li class='list-group-item'></li>" ).text( ui.draggable.text()).attr("name", ui.draggable.attr("name")).appendTo( this ).draggable({
						  appendTo: "body",
						  helper: "clone"
			  		 });
      }
    }).sortable({
      items: "li:not(.placeholder)",
      sort: function() {
        // gets added unintentionally by droppable interacting with sortable
        // using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
        $( this ).removeClass( "ui-state-default" );
    }});
    
    $('#trashExtras').droppable({  
		activeClass: "ui-state-default",
		hoverClass: "ui-state-hover",
		accept: ":not(.ui-sortable-helper)",
		drop: function( event, ui ) {
			//if name==id then remove all and delete from json
			if($(ui.draggable).attr("name")==$(ui.draggable).attr("id")	){
				$('[name="'+$(ui.draggable).attr("name")+'"]').remove();
				var extraName = $(ui.draggable).attr("name")
				var extraId=extraName.match(/\d+/g);
				var z;
				//delete from json
				for(z=0;z<window.Extras.length;++z){
					if(window.Extras[z].id == extraId){
						window.Extras.splice(z, 1);
						z--;
					}
				}
					
			} else {
				//just remove the <li>
				$(ui.draggable).remove();
			}
		}
	})
	$('#extraDocket').find('li').click(function(){
			var extraId = $(this).attr("id")
			extraId=extraId.match(/\d+/g);
			
			theExtra = $.grep( window.Extras, function(e) {
					return e.id == extraId;
			})[0];
			
			var extraName=theExtra.name;
			var extraQuestion = theExtra.question;
			var extraType = theExtra.type;
			var extraOptions = theExtra.options;
			
			$('#ExtraId').val(extraId);
			$('#ExtraName').val(extraName);
			$('#ExtraQuestion').val(extraQuestion);
			$('#ExtraError1').hide();
			$('#ExtraType').val(extraType);
			$('#ExtraHasExtras').prop('checked', false);
			$('.extraHasExtras').hide()
			$('#ExtraOptions').find('li').not('#option_prototype').remove();
			
			var i;
			for(i=0;i<extraOptions.length;++i){
				var newOption = expandExtra();
				$(newOption).find('[name="name"]').val(extraOptions[i].name);
				$(newOption).find('[name="price"]').val(extraOptions[i].price);
				
				$.each(extraOptions[i].extras, function(key, value){
						$('.extraHasExtras').show()
						var optionExtraId= value;
						var extraList = $(newOption).find('[name="extraExtras"]')
						
						var optionExtra =$.grep( window.Extras, function(e) {
								return e.id == optionExtraId;
						})[0];
						
						
						$( '<li class="list-group-item"></li>' ).text(optionExtra.name).attr("name", "extra_"+optionExtraId).appendTo(extraList).draggable({
							 appendTo: "body",
							 helper: "clone"	
						})
						
				})
				//add in the extras
			}
			$('#NewExtra').modal('show');
			
		});
    
    
    
    $('.extraHasExtras').hide();
    
		PlaceHolder();
		
		//ConstructMenu(menu);
});	


	


