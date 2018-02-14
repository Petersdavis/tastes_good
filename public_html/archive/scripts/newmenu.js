//Global Variable Initializations


//Site Specific Functions\



function initializeAddCategory(){
	$('#NewCategoryName').val("New Category Name");
       
}

function initializeAddItem(){
	$('#newItem_product').val("Item Name");
	$('#newItem_price').val("0.00");
	$('#newItem_description').val("");
}

function initializeAddExtra(){
	$('#preview_select').empty().hide();
	$('#preview_check').empty().hide();
	$('#preview_question').text("");
	$('#ExtraError1').addClass('hidden');
	$('#ExtraId').val("new");
	$('#ExtraName').val("");
	$('#ExtraQuestion').val("");
	$('#ExtraError1').hide();
	$('#ExtraType').val("1");
	$('#typeOne').removeClass("btn-default").addClass("btn-primary");
	$('#typeTwo').removeClass("btn-primary").addClass("btn-default");	
			

	$('#ExtraOptions').find('li').not('#option_prototype').remove();
	
}



function AddCategory(){
	newDiv = $('#category_prototype').clone();  
	newDiv.appendTo('#rest_categories');
	
	return newDiv;
	
}


function NewCategory(Category){
	requestSave();
	
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
	$(new_cat).find('[name="category_id"]').val(window.lastCategory);
	
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
		var cat_id = $(cat).find('[name=category_id]').val();
		var category = $(cat).find('[name=category]').val();
		$('#newItem_category').text(category);
		$('#newItem_category_id').text(cat_id);
			
	});
	
	$(new_cat).find('[name=Delete_Cat]').click(function(){
			requestSave();
			var cat = upTo(this, "li");
			$(cat).remove();
			
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
		requestSave();
		//form validation
		$('#prototypes').append($("#extra_list_placeholder")[0])
		
		
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
		lastExtra=lastExtra+1;
		var extraId = lastExtra;
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
			
			var opt_price = /[\d]+[.]{0,1}[\d]{0,2}/.exec($(this).find('[name="price"]').val())[0]
			var option = {'name':$(this).find('[name="name"]').val(), 'price': opt_price, 'extras':optionExtra};
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
			initializeAddExtra();
			var extraId = $(this).attr("id")
			extraId=extraId.match(/\d+/g);
			
			theExtra = $.grep( window.Extras, function(e) {
					return e.id == extraId;
			})[0];
			
			var extraName=theExtra.name;
			var extraQuestion = theExtra.question;
			var extraType = theExtra.type;
			
			if(extraType == "1"){
				$('#typeOne').removeClass("btn-default").addClass("btn-primary");
				$('#typeTwo').removeClass("btn-primary").addClass("btn-default");	
			}else if (extraType == "2"){
				$('#typeTwo').removeClass("btn-default").addClass("btn-primary");	
				$('#typeOne').removeClass("btn-primary").addClass("btn-default");
			}
			
			var extraOptions = theExtra.options;
			
			$('#ExtraId').val(extraId);
			$('#ExtraName').val(extraName);
			$('#ExtraQuestion').val(extraQuestion);
			$('#ExtraError1').hide();
			$('#ExtraType').val(extraType);
			$('#ExtraOptions').find('li').not('#option_prototype').remove();
			
			var i;
			for(i=0;i<extraOptions.length;++i){
				var newOption = expandExtra();
				$(newOption).find('[name="name"]').val(extraOptions[i].name);
				$(newOption).find('[name="price"]').val(extraOptions[i].price);
				
				$.each(extraOptions[i].extras, function(key, value){
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
	$('#extra_'+extraId).unbind("click").click(function(){
			initializeAddExtra();
			var extraId = $(this).attr("id")
			extraId=extraId.match(/\d+/g);
			
			theExtra = $.grep( window.Extras, function(e) {
					return e.id == extraId;
			})[0];
			
			var extraName=theExtra.name;
			var extraQuestion = theExtra.question;
			var extraType = theExtra.type;
			
			if(extraType == "1"){
				$('#typeOne').removeClass("btn-default").addClass("btn-primary");
				$('#typeTwo').removeClass("btn-primary").addClass("btn-default");	
			}else if (extraType == "2"){
				$('#typeTwo').removeClass("btn-default").addClass("btn-primary");	
				$('#typeOne').removeClass("btn-primary").addClass("btn-default");
			}
			
			var extraOptions = theExtra.options;
			
			$('#ExtraId').val(extraId);
			$('#ExtraName').val(extraName);
			$('#ExtraQuestion').val(extraQuestion);
			$('#ExtraError1').hide();
			$('#ExtraType').val(extraType);
			
			$('#ExtraOptions').find('li').not('#option_prototype').remove();
			
			var i;
			for(i=0;i<extraOptions.length;++i){
				var newOption = expandExtra();
				$(newOption).find('[name="name"]').val(extraOptions[i].name);
				$(newOption).find('[name="price"]').val(extraOptions[i].price);
				
				$.each(extraOptions[i].extras, function(key, value){
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
			var opt_price = /[\d]+[.]{0,1}[\d]{0,2}/.exec($(this).find('[name="price"]').val())[0]
			var option = {'name':$(this).find('[name="name"]').val(), 'price': opt_price, 'extras':optionExtra};			
			extraOptions.push(option);
		})
		
		
		window.Extras[jsonKey] = {'id':extraId, 'name':extraName, 'question':extraQuestion, 'type':extraType, 'options':extraOptions}
	
	}
		
	
	 $('#extra_'+extraId).draggable({
      appendTo: "body",
      helper: "clone"
    });
}

function requestSave(){
	$('#submitform').removeClass("btn-default");
	$('#submitform').addClass("btn-warning");
	
	$(window).bind("beforeunload", function (e) {
    var confirmationMessage = 'It looks like you have been editing something. '
                            + 'If you leave before saving, your changes will be lost.';

    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
});
}
	
	



function generatePreview(){
	//initialize
	$('#ExtraError1').addClass('hidden');
	$('#preview_select').empty().hide();
	$('#preview_check').empty().hide();
	
	var Type = $('#ExtraType').val();
		
	//set the question:
	$('#preview_question').text( $('#ExtraQuestion').val() );
	
	switch (Type){
		case "0":
			$('#ExtraError2').removeClass('hidden');
			$('#ExtraError2').show();
			break;
		case "1":
			
			//buttons
			$('#preview_select').empty().show();
			$("#preview_continue").hide();
			$('#NewExtra').find('[name="option"]').each(function( index, element ){
					var price= /[\d]+[.]{0,1}[\d]{0,2}/.exec($(this).find('[name="price"]').val())[0]
					var extra_btn = $('<button></button>')
					$(extra_btn).addClass("btn btn-block btn-default").text($(this).find('[name="name"]').val()).append('<strong style="float:right; margin-right:20px;">$' +price+'</strong>');
					$('#preview_select').append(extra_btn);		
			})
			
			
			break;
		case "2":
			//options
			$('#preview_check').empty().show();
			$("#preview_continue").show();
			$('#NewExtra').find('[name="option"]').each(function( index, element ){
					var price =  /[\d]+[.]{0,1}[\d]{0,2}/.exec($(this).find('[name="price"]').val())[0]
					$('#preview_check').append('<label class="checkbox-inline"><input type="checkbox">'+ $(this).find('[name="name"]').val() +' <strong>'+ price +'</strong></input></label>')
				
			})
			
			
			break;
	}
	
}



function NewItem(Category_Id, Category, Product, Price, Description){
	requestSave();
	
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
	
	$(new_item).find('[name="item_id"]').val(window.lastItem);
	$(new_item).find('[name="item_product"]').val(Product);
	$(new_item).find('[name="item_price"]').val(Price).attr("placeholder", Price);
	$(new_item).find('[name="item_category"]').val(Category);
	$(new_item).find('[name="item_description"]').val(Description);
	
	$(new_item).find('[name="itemDocket"]').droppable({ activeClass: "ui-state-default",
				  hoverClass: "ui-state-hover",
				  accept: ":not(.ui-sortable-helper)",
				  drop: function( event, ui ) {
				  	  requestSave();
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
		requestSave();
		var item = upTo(this, 'li');
		$(item).remove();
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
	requestSave();
	
	var div = upTo(event.target, "li");
	$(div).remove()
	
}

	
 function PromoteCategory(category){
	//just move the li.
}

function SubmitForm(){	
	
		$('#prototypes').append($("#extra_list_placeholder")[0])
	
		//categories JSON
		var categories = []
		var category = {}
		var itm = {}
		var catCollection=document.getElementsByName("categories");
		
		$.each($('[name="categories"]').not('#category_prototype'), function(key, item){
				var catId = $(this).find('[name="category_id"]').val();
				var catCategory = $(this).find('[name="category"]').val();
if (catCategory == ""){
 catCategory = $(this).find('[name="category"]').attr("placeholder");
}
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
if (itmProduct == ""){
itmProduct = $(this).find('[name="item_product"]').attr("placeholder");
}

					var itmPrice = $(this).find('[name="item_price"]').val();
if (itmPrice ==""){
 itmPrice=$(this).find('[name="item_price"]').attr("placeholder");
}

					var itmDescription = $(this).find('[name="item_description"]').val();
if (itmDescription==""){
    itmDescription = $(this).find('[name="item_description"]').attr("placeholder");
}

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
		formData.append ('rest_id', window.rest_id);
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
		    console.log(xhr.response);
		   
		    $('#submitform').removeClass("btn-warning");
		    $('#submitform').addClass("btn-default");
		    
		   $(window).unbind('beforeunload');
		    
		    var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
			checklist.menu = 1;
			localStorage.setItem("TGchecklist", JSON.stringify(checklist));
			//get global variables
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/menu.php', true);
		xhr.send(formData);
		
		if($('#extraDocket').find( "li" ).length==0){
			$('#extraDocket').append($('#extra_list_placeholder')[0])
		}  
		//Action to the Different Forms
		
	  
}
	
	
//stop forms from changing page
document.addEventListener("DOMContentLoaded", function(event) { 
		$('#VidTutorial').on('hidden.bs.modal', function () {
			var video = $("#tutorial_vid").attr("src");
			$("#tutorial_vid").attr("src","");
			$("#tutorial_vid").attr("src",video);
		});
		
		$('#typeOne').click(function(){
			$('#typeOne').removeClass("btn-default").addClass("btn-primary")	
			$('#typeTwo').removeClass("btn-primary").addClass("btn-default")	
			$('#ExtraType').val("1");	
		});
		
		$('#typeTwo').click(function(){
			$('#typeTwo').removeClass("btn-default").addClass("btn-primary")	
			$('#typeOne').removeClass("btn-primary").addClass("btn-default")	
			$('#ExtraType').val("2");	
		});
		
		$('[name=item_price], [name=price], [name=item_description], [name=item_product]').change(function(){
			requestSave();
						
			//or go discard change
		});	
		
		$('[name="Promote_Cat"]').click(function(){
			requestSave();
				
			var cat = upTo(this, "li");
			if($(cat).prev().length>0){
				$(cat).insertBefore($(cat).prev());
			}
		});
				
				
		
		$('[name="Change_Cat"]').click(function(){
				var cat_name = prompt("Please the category name", $(this).parent().parent().find('[name="label"]').text());
				if(cat_name){
					requestSave();	
					
					$(this).parent().parent().find('[name="label"]').text(cat_name);
					$(this).parent().parent().find('[name="category"]').val(cat_name);
				}
		});
			
			
		$('[name=NewCategory]').click(function(){
				initializeAddCategory();
		});
		
		$('[name=CreateExtra]').click(function(){
				initializeAddExtra();
				expandExtra();
		});
		
		
		$('[name=Category_Items]').hide();
		
		$('[name=Delete_Cat]').click(function(){
			requestSave();
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
			requestSave();
			
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
				requestSave();
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
      	  requestSave();
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
      	requestSave();
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
      	  requestSave();
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
			requestSave();
			//if name==id then remove all and delete from json
			if($(ui.draggable).attr("name")==$(ui.draggable).attr("id")){
				var extraName = $(ui.draggable).attr("name")
				var CatDocket = [];
				var CatLi = [];
				CatLi =$('[name="categoryDocket"]').find($('[name="'+extraName+'"]'))
				var k = 0
				for(k=0;k<CatLi.length;++k){
					CatDocket[k]=upTo(CatLi[k],"ul");
				}
				var ItmDocket = []
				var ItmLi = []
				ItmLi =$('[name="itemDocket"]').find($('[name="'+extraName+'"]'))
				var j = 0
				for(j=0;k<ItmLi.length;++j){
					ItmDocket[j]=upTo(ItmLi[j],"ul");
				}
				
				var extraId=extraName.match(/\d+/g);
				var z;
				//delete from json
				for(z=0;z<window.Extras.length;++z){
					if(window.Extras[z].id == extraId){
						window.Extras.splice(z, 1);
						z= z-1;
					}
				}
				$('[name="'+extraName+'"]').remove();	
				for(k=0;k<CatDocket.length;++k){
					if($(CatDocket[k]).find( "li" ).length==0){
					$('#cat_list_placeholder').clone().attr("id", "").appendTo( CatDocket[k] );
					}   	
				}
				for(k=0;k<ItmDocket.length;++k){
					if($(ItmDocket[k]).find( "li" ).length==0){
					$('#itm_list_placeholder').clone().attr("id", "").appendTo( ItmDocket[k] );
					}   	
				}
				
				if($('#extraDocket').find( "li" ).length==0){
					$('#extraDocket').append($('#extra_list_placeholder')[0])
				}                         
			} else {
				//just remove the <li>
				var docket =  upTo(ui.draggable[0], "ul")
				$(ui.draggable).remove();
				if( $(docket).find("li").length == 0){
					if($(docket).attr("name") == "itemDocket"){
						$('#itm_list_placeholder').clone().attr("id", "").appendTo( docket );
					}else if ($(docket).attr("name") == "categoryDocket"){
						$('#cat_list_placeholder').clone().attr("id", "").appendTo( docket );
					}
					
				}
				
			}
		}
	})
	$('#extraDocket').find('li').click(function(){
			initializeAddExtra()
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
			
			if(extraType == "1"){
				$('#typeOne').removeClass("btn-default").addClass("btn-primary");
				$('#typeTwo').removeClass("btn-primary").addClass("btn-default");	
			}else if (extraType == "2"){
				$('#typeTwo').removeClass("btn-default").addClass("btn-primary");	
				$('#typeOne').removeClass("btn-primary").addClass("btn-default");
			}
		
			
			
			
			$('#ExtraOptions').find('li').not('#option_prototype').remove();
			
			var i;
			for(i=0;i<extraOptions.length;++i){
				var newOption = expandExtra();
				$(newOption).find('[name="name"]').val(extraOptions[i].name);
				$(newOption).find('[name="price"]').val(extraOptions[i].price);
				
				$.each(extraOptions[i].extras, function(key, value){
						
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
    
    if($('#extraDocket').find( "li" ).length==0){
		$('#extraDocket').append($('#extra_list_placeholder')[0])
	}
    
  
    		
		//ConstructMenu(menu);
});	


	


