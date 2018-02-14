//Global Variable Initializations
Coupon = {"title":"", "discount":0, "type":"discount", "price":"0.00", "rest_id":rest_id, "extras":[], "public":1, "expire":3}
//Site Specific Functions\

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
			var opt_price = /[\d]+[.]{0,1}[\d]{0,2}/.exec($(this).find('[name="price"]').val())[0]			
			var option = {'name':$(this).find('[name="name"]').val(), 'price':opt_price, 'extras':optionExtra};
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




function generateCoupon(){	
		Coupon.type = $('input:radio[name=coupon_type]:checked').val();
		if(Coupon.type == "discount"){
			Coupon.title =  $("#discount_percent").val() + " Discount"
			Coupon.price = 0
			Coupon.discount =  $('input:radio[name=discount_percent]:checked').val();
			
			
		} else {
			Coupon.discount = 0
			Coupon.title = $('#coupon_title').val();
			Coupon.price = $('#coupon_price').val();
			Coupon.price = /[\d]+[.]{0,1}[\d]{0,2}/.exec(Coupon.price)[0]
			
			Coupon.extras = [];
			
			$.each($('#coupon_Docket').find('li').not('.placeholder'), function(key, item){
					var extraId = $(this).attr("name")
					extraId=extraId.match(/\d+/g);
					if(extraId.length > 0){
						Coupon.extras.push(extraId[0]);
					}
				})
			
		}
		if( $('input:radio[name=public_coupon]:checked').val()=="public"){
			Coupon.public =  1;
		}else{Coupon.public =  0;}
		
		Coupon.expire = $('input:radio[name=coupon_expire]:checked').val();
			
		var coupon = JSON.stringify(Coupon);
		var extras =JSON.stringify(Extras);
		
		var formData = new FormData ();
					
		formData.append ('coupon', coupon);
		formData.append ('extras', extras);
		formData.append ('rest_id', window.rest_id);
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
		    var result = xhr.response;
		    result = JSON.parse(result);
		    
		    if(result.result == "success"){
		    	var result = JSON.parse(xhr.response);
		    	
		    	if(result.result == "success"){
		    		var coupon = result.coupon;
		    		if(coupon.type == "item"){
		    			coupon.title = coupon.title + ": <span style='color: #633E26;'>$" + coupon.price + "</span>";
		    			
		    			
		    		}
		    		title_text = $("<span>"+coupon.title+"</span>");
		    		
		    		if(coupon.public == 1){
		    			coupon.public = "PUBLIC"
		    		}else {
		    			coupon.public = "PRIVATE"
		    		}
		    			
		    		
		    		var div = $('#coupon_prototype').clone();
		    		$(div).attr('id', "coupon_"+coupon.id).find('[name="prototype_link"]').attr("href", coupon.link).end()
		    											  .find('[name="prototype_title"]').empty().append(title_text).end()
		    											  .find('[name="prototype_code"]').text(coupon.code).end()
		    											  .find('[name="prototype_public"]').text(coupon.public).end()		  
		    											  .find('[name="prototype_expires"]').text(coupon.expires).end()
		    											  .find('[name="prototype_delete"]').click(function(){
		    											  	var delete_coupon = upTo(this, "li");
															var delete_id = $(delete_coupon).attr("id");
															delete_id = delete_id.match(/\d+/g);
															
															if(delete_id.length>0){
																deleteCoupon(delete_id[0]);
															}
															
															$(delete_coupon).remove();
		    											  		  
		    											  });
		    											  
		    		$('#coupon_list').prepend(div);
		    		$("html, body").animate({ scrollTop: $(document).height() }, "slow");
		    		
		    	}
		    var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
			checklist.coupon = 1;
			localStorage.setItem("TGchecklist", JSON.stringify(checklist)); 	
		    	
		    }	
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/create_coupon.php', true);
		xhr.send(formData);  
}

function deleteCoupon(coupon_id){
	var formData = new FormData ();
					
		formData.append ('coupon', coupon_id);
		formData.append ('rest_id', window.rest_id);
				
		var xhr_delete = new XMLHttpRequest();
		
		xhr_delete.onload = function() {
			
		    console.log(xhr_delete.response);
		}	
	
		// Open the connection.
		xhr_delete.open('POST', 'https://'+ location.hostname + '/uploader/delete_coupon.php', true);
		xhr_delete.send(formData);
	
}

	


//stop forms from changing page
document.addEventListener("DOMContentLoaded", function(event) {
		$('[name="deleteCoupon"]').click(function(){
				var delete_coupon = upTo(this, "li");
				var delete_id = $(delete_coupon).attr("id");
				delete_id = delete_id.match(/\d+/g);
				
				if(delete_id.length>0){
					deleteCoupon(delete_id[0]);
				}
				
				$(delete_coupon).remove();
				
				
		});
		
		$('#create_coupon').click(function(){
				generateCoupon();
		});		
			
		$('#coupon_Docket').droppable({ 
				activeClass: "ui-state-default",
				hoverClass: "ui-state-hover",
				accept: ":not(.ui-sortable-helper)",
				drop: function( event, ui ) {
					 $( this ).find( ".placeholder" ).remove();
					 $( "<li class='list-group-item'></li>" ).text(ui.draggable.text()).attr("name", ui.draggable.attr("name")).appendTo( this ).draggable({
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

		
		
		$('input:radio[name=coupon_type]').click(function() { 
			var value = $('input:radio[name=coupon_type]:checked').val();
			if (value=="discount"){
				$('#discount').show();
				$('#item').hide();
				
			}else{
				$('#discount').hide();
				$('#item').show();
			}
						
		});
		
		$('input:radio[name=discount_percent]').click(function() { 
			var value = $('input:radio[name=discount_percent]:checked').val();
			var percent = value * 100;
			percent = percent + "%";
			
			$('#discount_percent').val(percent)
						
		});
		
		$('[name=CreateExtra]').click(function(){
				initializeAddExtra();
		});
		
		
		
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
      
      
    $('#trashExtras').droppable({  
		activeClass: "ui-state-default",
		hoverClass: "ui-state-hover",
		accept: ":not(.ui-sortable-helper)",
		drop: function( event, ui ) {
			//if name==id then remove all and delete from json
			if($(ui.draggable).attr("name")==$(ui.draggable).attr("id")	){
				alert("Extras can only be deleted from your primary menu");
				return;
				
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


	


