//Global Variable Initializations

var hostname = location.hostname
var nonce = {}
var pagename = "order.php";




//Site Specific Functions\
function cancelItem(){
	
	
}


function initDetails(){
if (user.user_id !== 0){
			
	Order.user = user;
	
	$('#Cust_name').text(user.fname + " "+ user.lname);
	$('#Cust_email').text(user.email);
	$('#Cust_phone').text(user.phone);
	
	$('#order_AddressList').empty();
	if(user.addresses.length==0){
		var placeholder= $("<div>Address is required for deliveries.</div>");
		$(placeholder).attr("id", "address_placeholder");
		$('#order_AddressList').append(placeholder);
	}
	
	 for(x=0;x<user.addresses.length;++x){
		 addAddress(user.addresses[x], $('#order_AddressList'));
		 
	 }
	 if (user.addresses.length>0){
	 	 $('#address_radio_'+user.addresses[0].id).prop("checked", true);
	}

	$('#orderDetails').modal('show');
	
	
	var driverTip = (parseFloat(Order.subTotal) * parseFloat($("[name=deliveryTip]:checked").val())).toFixed(2);	
	
	driverTip = validatePrice(driverTip, "$0.00");
	
	$('#deliveryTip').val(driverTip);
	
	$('#delivery_address_new').hide();
	$('#saveAddress').hide();
	
	$('#order_Pickup').prop("checked",true);
	$('#delivery_address').hide();
	$('#delivery_address_buttons').hide();
	$('#driver_tip').hide();
	$('[name = "addressType"]').change(function(e){
			var addressType = $("[name=addressType]:checked").val();
			if(addressType == "appt"){
				$('#appt_details').show();
			} else {
				$('#appt_details').hide();
			}
	});
	
}else{
	$('#userLogin').modal('show').on('hidden.bs.modal', function(e){
			if(user.user_id != 0){
				initDetails();
			}
			$(this).unbind(e);
		});		
		
}

}




function RevealItems(){
	var thisCategory = upTo(event.target, "li");
	thisCategory = thisCategory.id.replace( /(^.*\[|\].*$)/g, '' );
	var div=document.getElementById("category["+thisCategory+"]_items");
	
	
	if(div.className.indexOf("hidden")>-1){
		div.removeAttribute("class");
	}else{
		div.setAttribute("class", "hidden");
	}
	
}

function TreeSearch(object){
	var y;
	var z;
	
	for(y=0;y<object.selected.length;++y){
		for(z=0;z<object.selected[y].extras.length;++z){
		  if(object.selected[y].extras[z].selected.length==0){
		  	theExtra=object.selected[y].extras[z];
		  	extra_id=object.selected[y].extras[z].extra_id;
		  	}else{
		  	   TreeSearch(object.selected[y].extras[z]);  
			}		  
	     
			if(extra_id!==0){
			  break;
			}
		
		} 
		if(extra_id!==0){
		  break;
		}  	  
	}
}
function calcPrice(extra){
	var i;
	var h;
	var this_price = 0;
	
	for (i=0;i<extra.selected.length;++i){
		this_price = (parseFloat(this_price) + parseFloat(extra.selected[i].price)).toFixed(2);
		if(extra.selected[i].extras.length > 0 ){
			for(h=0;h<extra.selected[i].extras.length;++h){
			  this_price = (parseFloat(this_price) + parseFloat(calcPrice(extra.selected[i].extras[h]))).toFixed(2);	
			}
	    }  
	}
	return this_price;
	
}

function getExtra(){
	extra_id = 0
	theExtra = {}
	//get the first empty extra
	for(x=0;x<order_Item.extras.length;++x){
		if(order_Item.extras[x].selected.length==0){
		extra_id=order_Item.extras[x].extra_id;
		theExtra=order_Item.extras[x]
		break;
		
		}else{
			TreeSearch(order_Item.extras[x]);
		}
	
		if(extra_id!==0){
			break;
		} 
	}
	
	if(extra_id!==0){
		$('#ExtraModal').modal('show');
		launchModal(extra_id, theExtra);
		
	}else{
		// Print the Order
		if(OrderTotal==0){
			$('[name="order_placeholder"]').hide();
		}
		
		++OrderTotal;
	        Order.items.push(order_Item);
	        
		
		$('[name="order_placeholder"]').hide();
		var indent = 0;
		protoOrder = $('#order_proto').clone();
		$('#current_orders').append(protoOrder)
		
		$(protoOrder).attr('id', "Order_"+order_Item.id);
		
		
		$(protoOrder).find('[name="delete_order"]').on('click', {id:order_Item.id}, function(event){
							
							$('#current_orders').find('#Order_'+ event.data.id).remove();
							removeByAttr(Order.items, 'id', event.data.id);				
							OrderTotal = OrderTotal-1
							if(OrderTotal==0){
									$('[name="order_placeholder"]').show();
							}
							
							var subtotal = 0;
							for(k=0;k<Order.items.length;++k){
								subtotal = (parseFloat(subtotal)+parseFloat(Order.items[k].price)).toFixed(2);
								
								if (Order.items[k].extras.length >0){
									for(j=0;j<Order.items[k].extras.length;++j){
										var extra_total = calcPrice(Order.items[k].extras[j])
										subtotal = (parseFloat(subtotal)+parseFloat(extra_total)).toFixed(2);
									}
								}
							}
							Order.subTotal = subtotal;
							$('#order_subTotal').text('$'+Order.subTotal);
							var cur_discount = 0
							if(Order.discount_rate){
								var cur_discount = parseFloat(Order.subTotal*Order.discount_rate).toFixed(2);
								$('#order_discount').text('$' + cur_discount);
							}
							var tax = parseFloat((Order.subTotal - cur_discount)*0.13).toFixed(2);
							$('#order_tax').text('$'+tax);	
							var total = (parseFloat(Order.subTotal) + parseFloat(tax) - parseFloat(cur_discount)).toFixed(2);
							$('#order_total').text('$'+total);	
							$('#xs_total').text('$'+total);
							
							
					}).end()
							
					 .find('[name="item_id"]').text(order_Item.item_id).end()
					 .find('[name="item_product"]').text(order_Item.product).end()
					 .find('[name="item_price"]').text('$'+order_Item.price);
					 
			Order.subTotal = (parseFloat(Order.subTotal) + parseFloat(order_Item.price)).toFixed(2);
			if (order_Item.extras.length == 0){
				$(protoOrder).find('[name="item_extras"]').hide();
			}else{
				for(x=0;x<order_Item.extras.length;++x){
					if(order_Item.extras[x].selected[0].select_id == -1){
						continue;
					}		
					indent=indent+10;
					$(protoOrder).find('[name="item_extras"]').append(printTree(order_Item.extras[x], indent));
					indent=indent-10;
				}
			}
		$('#order_subTotal').text('$'+Order.subTotal);
		
		var cur_discount = 0
		if(Order.discount_rate){
			var cur_discount = parseFloat(Order.subTotal*Order.discount_rate).toFixed(2);
			$('#order_discount').text('$' + cur_discount);
		}
			
			
		
		var tax = parseFloat((Order.subTotal - cur_discount)*0.13).toFixed(2);
		$('#order_tax').text('$'+tax);	
		var total = (parseFloat(Order.subTotal) + parseFloat(tax) - parseFloat(cur_discount)).toFixed(2);
		$('#order_total').text('$'+total);	
		$('#xs_total').text('$'+total);
		$('#xs_total').parent().animate({height: "+=10", width: "+=5"}, 150,"easeOutSine").animate({height: "-=10",width: "-=5"}, 50, "easeInQuint");
		
		$('#ExtraModal').modal('hide');	
		}
}

function printTree(extra, indent){
	var y;
	var z;
	var dom_object = $("<li class='list-group-item no-padding no-border'></li>");
	
	var extras = $("<ul class = 'list-group no-padding no-border'></ul>");
	for (y=0;y<extra.selected.length;++y){
		if(extra.selected[y].select_id == -1){
			continue;	
		} else {
		var curr_div = $("<div style='padding-left:"+indent+"px;'></div>").append("<span >"+extra.selected[y].name+"</span>","<span style='color:#633E26;float:right;padding-right:10px;'>$"+extra.selected[y].price+"</span>")	
		$(dom_object).append(curr_div);
		Order.subTotal = (parseFloat(Order.subTotal) + parseFloat(extra.selected[y].price)).toFixed(2);
		
		if(extra.selected[y].extras.length > 0 ){
				
			for(z=0;z<extra.selected[y].extras.length;++z){
			  indent=indent+10;
			  $(extras).append(printTree(extra.selected[y].extras[z], indent));	
			  indent=indent-10;
			}
			
	     $(dom_object).append($(extras));
	    }  
	    
	   }
	}
	
	return dom_object;
}


function launchModal(extra_id, theExtra){

	
	//initialize the modal
	$('#extraSelect').empty().addClass('hidden');
	$('#extraCheck').empty().addClass('hidden');
	
	//set the question:
	$('#extraQuestion').text(theExtra.question );
	$('#parentItem').text(order_Item.product); 
	//set the options
	switch (theExtra.type){
		case "1":
			
			// buttons
			$('#extraSelect').removeClass('hidden');
			$('#extraContinue').text('No Thanks');
			$('#extraContinue').hide();
			//change continue button
			
			for(x=0;x<theExtra.options.length;++x){
				var extra_btn = $('<button></button>')
				$(extra_btn).addClass("btn btn-block btn-default").text(theExtra.options[x].name).append('<strong style="float:right; margin-right:20px;">$' +theExtra.options[x].price +'</strong>');
				$('#extraSelect').append($(extra_btn));
				
				$(extra_btn).click({option:theExtra.options[x], id:x}, function(event){
					
					var selection = {"select_id":event.data.id , "price":event.data.option.price, "name":event.data.option.name, "extras":[]};
					var y;
					for(y=0;y<event.data.option.extras.length;++y){
						selectExtra = $.grep(Extras, function(e) {
								return e.id == event.data.option.extras[y];
							})[0];
						selection.extras.push({'extra_id':selectExtra.id, 'selected':[],'options':selectExtra.options, 'question':selectExtra.question, 'type':selectExtra.type});
						
					}
				
				theExtra.selected.push(selection);
				getExtra();
				});
			
			}
			
			break;
		case "2":
			$('#extraCheck').removeClass('hidden');
			
			for(x=0;x<theExtra.options.length;++x){
				var li = $('<li></li>');
				li.addClass('list-group-item');
				li.append('<label class="checkbox-inline"><input type="checkbox">'+  theExtra.options[x].name +' </input></label>');
				li.append('<span style="float:right;"><strong>'+theExtra.options[x].price  +'</strong></span>');
				$('#extraCheck').append(li)
			}
			
			$('#extraContinue').text('Continue').show();	
			$('#extraContinue').off('click').click({extra:theExtra}, function(event){
				var x;
				for(x=0;x<event.data.extra.options.length;++x){
				   if($($('#extraCheck').find("input")[x]).prop('checked')){
				   	   var theOption_id = x;
				   	   var theOption= event.data.extra.options[x];
				   	   var selection={'select_id':x, 'price':theOption.price, 'name':theOption.name, 'extras':[]};
				   	   
				   	   for(z=0;z<theOption.extras.length;++z){
							selectExtra = $.grep( window.Extras, function(e) {
									return e.id == theOption.extras[z];
							})[0];
							
							selection.extras.push({'extra_id':selectExtra.id, 'selected':[], 'options':selectExtra.options, 'question':selectExtra.question, 'type':selectExtra.type});
							
						}
						
						theExtra.selected.push(selection);
					}
				}
				
				if(theExtra.selected.length==0){
					selection={'select_id':-1, 'price':0, 'name':"", 'extras':[]};
					theExtra.selected.push(selection);
				}
				getExtra();	
						
			});
			//options
			break;
	}	
}



function AddOrder(item_id){
	//get theCategory and theItem
	var x = 0;
	var y = 0;
	
	theCategory = ""
	theItem = ""
	theExtra = {}
	order_Item = {}
	
	for(x=0;x<window.Categories.length;++x){
		for(y=0;y<window.Categories[x].items.length;++y){
			if(window.Categories[x].items[y].id == item_id){
				theCategory = window.Categories[x];
				theItem = window.Categories[x].items[y];
				break;
			}			
		}
		if(theCategory !== ""){
			break;
		}
	}
	
		
	order_Item = {'id':OrderTotal+1, 'item_id':theItem.id, 'product':theItem.product, 'price':theItem.price, 'extras':[], 'subtotal':theItem.price};
		
	// build the Extra Cue
	
	if(theCategory.extras){
		for(x=0;x<theCategory.extras.length;++x){
			theExtra = $.grep( window.Extras, function(e) {
					return e.id == theCategory.extras[x];
			})[0];
			
			order_Item.extras.push({'extra_id':theExtra.id, 'selected':[], 'options':theExtra.options, 'question':theExtra.question, 'type':theExtra.type});
		}
	}
	
	if(theItem.extras){
		for(x=0;x<theItem.extras.length;++x){
			theExtra = $.grep( window.Extras, function(e) {
					return e.id == theItem.extras[x];
			})[0];
			
			order_Item.extras.push({'extra_id':theExtra.id, 'selected':[], 'options':theExtra.options, 'question':theExtra.question, 'type':theExtra.type});
			
		}
	}
	
	
	getExtra();
	
	
}

function addUser (user_id, user_name, active){
	if($('#user_'+user_id).length == 0){
		var user_div = $("<button class = 'list-group-item' name = 'user_login_button'  id = 'user_"+user_id+"'></button>").text(user_name);
		var button = $("<button type = 'button' name = 'remove_user_button' id = 'remove_user_"+user_id+"' class = 'close' style = 'padding-left:5px;padding-right:10px;font-size:20px;' aria-label = 'Close'></button>").append("<span aria-hidden='True'>&times;</span>");
		$(user_div).append(button);
		$('#user_list').append(user_div);	
				
			
		
		$("#remove_user_"+user_id).on("click", {'user_id':user_id}, function(e){
			e.stopPropagation();
			$('#user_'+e.data.user_id).remove();
			var splicePoint = -1;
			for(var x=0;x<TG_users.length;++x){
				if(TG_users[x].user_id == e.data.user_id){
					TG_users.splice(x, 1);
				}
			}
			localStorage.setItem("TG_users", JSON.stringify(TG_users));
			
			
			if (user.user_id !== 0 && e.data.user_id == user.user_id){
				$('#user_Continue').text('Select User to Continue').prop('disabled', true);
				
				//needs to logout 
				
			}
		});
	
	}else{
		//DO NOTHING
	}
	if (active == true){
		$('#user_list').find(".list-group-item").removeClass("active");
		$('#user_'+user_id).addClass("active");
		$('#user_Continue').text('Continue as '+user_name).prop('disabled', false);
	    	
	}
	
}



   


document.addEventListener("DOMContentLoaded", function(event) { 

	
  	//Check Open	
	
	
	if(!Rest.open || Rest.closed){
		$('#submit_Order').addClass("disabled").prop("disabled",true).attr("title", "This restaurant is currently closed!");
		$('#xs_confirm_btn').addClass("disabled").prop("disabled",true).attr("title", "This restaurant is currently closed!");
		$('#xs_preview_continue').addClass("disabled").prop("disabled",true).attr("title", "This restaurant is currently closed!");
		$('#rest_closed').modal("show");
		
	}
		
	$('#deliveryTip').change(function(){
		var driverTip = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#deliveryTip').val())[0]
		Order.driverTip = driverTip;
	});
		
	if(Order.coupon != 0){
	 	 if(Order.coupon.type == "discount"){
	 	 	 
	 	 	 Order.discount_rate = Order.coupon.discount;
	 	 	 //Order.discount
	 	 	 
	 	 	 //Show discount div. 
	 	 	 
	 	 	 
	 	 }else if (Order.coupon.type == "item"){
	 	 	 //Add the Item to the Menu
	 	 	 var coupon_item = {"category":"Coupons","description":"","id":"C"+Order.coupon.id,"product":Order.coupon.title +": $"+ Order.coupon.price, "price":Order.coupon.price, "extras":Order.coupon.extras};
	 	 	 Categories.push({"category":"Coupons", "items":[coupon_item],"extras":[]});
	 	 	 
	 	 	 
	 	 	 setTimeout( function(){AddOrder("C"+Order.coupon.id)}, 2000 ); 
	 	 	
	 	 	 
	 	 	 
	 	 }
	 	 
	 	 
	 
	 	 
	 	 
	 }
	 
	 
	 $('#saveAddress').click(function(){
	 		
			 geocodeAddress(geocoder);
	 });
 
		
	//initialize localstorage
					
	if (user.user_id !== 0){
			addUser(user.user_id, user.fname+ " " +user.lname, true)
	}
	
	
		
	//initialize datepicker
	
	$('#deliveryDate').datepicker({ hideIfNoPrevNext: true, minDate:-0, maxDate:"+7D"}).datepicker( "setDate", "+0" ).datepicker( "hide" );

	$(".ui-datepicker-current").remove();
	$('#ui-datepicker-div').hide();
	
	
	$('.timepicker').wickedpicker();
	

	
  //initialize buttons
  	$('#confirm_Order').click(function(){
  		if(	$("[name=deliveryOption]:checked").val()=="delivery"){
  			if(	$("[name=Order_Address]").length <=0){
  				alert("Address is required for deliveries.");
  				return true;
  					
  			}
  		}
  		
  		if($('#terms_conditions').is(':checked')){
  			
  			$('#orderProcess').removeAttr('disabled').removeClass("disabled")
  		}else{
  			$('#orderProcess').attr('disabled','disabled').addClass("disabled")
  		}
  			
  			
  			
  		$('#orderDetails').modal('hide');
  		$('#orderConfirm').modal('show');
  		
  		var cur_discount = 0
		if(Order.discount_rate){
			var cur_discount = parseFloat(Order.subTotal*Order.discount_rate).toFixed(2);
			$('#confirm_discount').text(validatePrice(cur_discount)).parent().show();
		}
		Order.discount = cur_discount;
  		
  		//store form-data to window.Order
  		if($('[name="deliveryOption"]:checked').val()=="delivery"){
  			Order.pref.deliveryOption = 1;
  			var address_id = $('[name="Order_Address"]:checked').val();
  			for(x=0;x<user.addresses.length;++x){
				if(user.addresses[x].id ==  address_id){
					Order.pref.deliveryAddress = user.addresses[x]
				}
			}
			var driverTip = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#deliveryTip').val())[0]
			Order.driverTip = driverTip;
			
			
			$('#cust_address_div').show();
			$('#cust_address').text(Order.pref.deliveryAddress.address);
			
			$('#confirm_tip_div').show();
			$('#confirm_delivery_div').show();
			
			$('#confirm_delivery').text(validatePrice(Order.pref.deliveryAddress.delivery));
			$('#confirm_tip').text($('#deliveryTip').val());
			Order.tax = ((parseFloat(Order.subTotal)+parseFloat(Order.pref.deliveryAddress.delivery) - parseFloat(Order.discount))*0.13).toFixed(2);
			$('#confirm_tax').text(validatePrice(Order.tax));
			Order.total = (parseFloat(Order.subTotal) + parseFloat(Order.tax) + parseFloat(Order.pref.deliveryAddress.delivery) + parseFloat(Order.driverTip) - parseFloat(Order.discount)).toFixed(2);
			$('#confirm_total').text(validatePrice(Order.total));
			$("#delivery_pref").empty();
			var delivery_pref =  $('<h3 style="text-transform: uppercase;"></h3>').text("DELIVERY");
			
		} else {
			Order.pref.deliveryOption = 0;
			Order.driverTip = 0;
			$('#confirm_delivery_tip_div').hide();
			$('#confirm_delivery_div').hide();
			$('#cust_address_div').hide();
			Order.tax = ((parseFloat(Order.subTotal)-parseFloat(Order.discount))*0.13).toFixed(2);
			$('#confirm_tax').text(validatePrice(Order.tax));
			Order.total = (parseFloat(Order.subTotal) + parseFloat(Order.tax)- parseFloat(Order.discount)).toFixed(2);
			$('#confirm_total').text(validatePrice(Order.total));
			$("#delivery_pref").empty();
			var delivery_pref =  $('<h3 style="text-transform: uppercase;"></h3>').text("PICK-UP");
		}
  		
		Order.pref.requestDate = $('#deliveryDate').val();
		Order.pref.requestTime = $('#deliveryTime').val();
		
		//initialize Order Confirm
		
		var user_info, delivery_pref, order_info;
		$('#cust_name').text(user.fname + "  " + user.lname);
		$('#cust_email').text(user.email);
		$('#cust_phone').text(user.phone);
		
		
		
		
		
		
		var inputDate = new Date($('#deliveryDate').val());
		var todayDate = new Date();
		
		if (inputDate.setHours(0,0,0,0) == todayDate.setHours(0,0,0,0)){
			inputDate = "Today";
		} else {
			inputDate = $('#deliveryDate').val()
		}
		
		inputTime =  $('#deliveryTime').val();
		
		var deliver_datetime = $('<h2></h2>').text("Requested for "+inputDate+" @ "+inputTime);
		
		$("#delivery_pref").append(delivery_pref).append(deliver_datetime);
		
		
		var order_info = $('#current_orders').clone()
		
		$('#order_info').empty().append(order_info);
		$(order_info).prop("id", "confirm_order_items");
		$(order_info).addClass("col-xs-12");
		
		$('#order_info').find('[name="delete_order"]').remove();
		
  		$('#confirm_subTotal').text($('#order_subTotal').text());
  		
  		$('#payment_Offline').prop("checked", true);
  		
	  		
  		 braintree.setup(btToken, "custom", {
			id: "braintreeForm",
			paypal:{
				container: "paypal_container",
				paymentMethodNonceInputField: "payment_nonce",
				amount: Order.total,
				currency: "CAD",
				onSuccess:function(payload){
					window.nonce = {"nonce":payload, "type":"paypal"};
					$('#credit_div').hide();
					$('#payment_method_div').show();
					$('#offline_buttons').show()
					
					//show the button
					
					
					
					
				},
				onCancelled:function(){
					window.nonce = {};
					$('#credit_div').show();
					$('#offline_buttons').hide()
					$('#orderConfirm').find('.modal-content').scrollTop(10000);
				}
			},
			hostedFields: {
			  number: {
				selector: "#card-number"
			  },
			  cvv: {
				selector: "#cvv"
			  },
			  expirationMonth: {
				selector: "#expiration-month"
			  },
			  expirationYear: {
				selector: "#expiration-year"
			  },
			  
			  styles: {
			  	'input': {
				      'font-size':'1.2em',
				      'font-family': 'Courier New',
				      'color': '#343238',
				 },
							  
				  
				  ".invalid": { color: "#990000" },
			  }
			},
			onReady:function(integration){
				checkout = integration;	
				$('#orderConfirm').on('hidden.bs.modal', function(e){
					checkout.teardown(function () {
						checkout = null;
						$('#paypal_prepend').show()
					});
					$(this).unbind(e);
				});		
				$('#payment_method_div').hide();
				$('#credit_div').show();
			},
			onPaymentMethodReceived:function(payload){
				nonce = payload
				$('#credit_div').hide();
				
				$('#payment_method_div').show();
							
				$('#offline_buttons').show()			
				$('#orderConfirm').find('.modal-content').scrollTop(10000);
				return false;
		
		  }
	}); 
  		
  		  	
		$('#online_payments').hide();
		$('#offline_buttons').show();
		
		$('[name = "payment_type"]').change(function(e){
				var paymentType = $("[name=payment_type]:checked").val();
				if(paymentType == "offline"){
					$('#online_payments').hide();
					$('#offline_buttons').show()
				} else {
					
					$('#online_payments').show();
					$('#credit_div').show();
					
					$('#payment_method_div').hide();
					
					$('#orderConfirm').find('.modal-content').scrollTop(10000);
					$('#offline_buttons').hide()
				}
		});
  		
		
		
  		
  		
  	});
  
  	$('#terms_conditions').change(function(){
  		if($('#terms_conditions').is(':checked')){
  			
  			$('#orderProcess').removeAttr('disabled').removeClass("disabled")
  		}else{
  			$('#orderProcess').attr('disabled','disabled').addClass("disabled")
  		}
  	
  	
  	});
  	
	$('#orderProcess').click(function(){
			$('#loading_gif').slideDown(200);
			Order.comments = $('#order_comments').val();
			var formData = new FormData ();
			formData.append ('order', JSON.stringify(Order));
			if(Order.paymentType == "online"){
				var Nonce_json = JSON.stringify(nonce);
				formData.append ('nonce', Nonce_json);
			}
			
			
									
			var xhr = new XMLHttpRequest();
			
			xhr.onload = function() {
				$('#loading_gif').hide();
				
				
				console.log(xhr.response);
				ret = JSON.parse(xhr.response)
				
				if(ret.result== "success"){
					$('#orderConfirm').modal("hide");
					$('#order_success').delay(300).modal("show");
					
				}else if(ret.result=="fail" && ret.error=="TRANSACT_FAIL") {
					$('#orderConfirm').modal("hide");
					$('#card_error').delay(300).modal("show");
				}else {
					$('#orderConfirm').modal("hide");
					$('#order_error').delay(300).modal("show");
				}
				
				
				
				
			};
			// Open the connection.
			xhr.open('POST', 'https://'+ hostname + '/uploader/order.php', true);
			xhr.send(formData);	
			
			
		});
	
	$('#submit_Order').click(function(){
		initDetails();		
	});
	
	$('#xs_confirm_btn').click(function(){
		initDetails();		
	});
	
	$('#xs_total_btn').click(function(){
		$('#xs_preview').modal('show');	
		var order_info = $('#current_orders').clone()
		$(order_info).attr("id", "xs_current_orders");
		$('#xs_preview_body').empty().append(order_info);
		var preview_totals = $('#preview_totals').clone();
		$(preview_totals).attr("id", "xs_preview_totals");
		$('#xs_preview_totals').empty().append(preview_totals);
		
		$('#xs_preview_body').find('[name="delete_order"]').click(function(){
			var li_item = upTo($(this)[0], "li");
			var the_id = $(li_item).attr("id");
			$(li_item).remove();
			
			var the_btn = $('#current_orders').find('#'+the_id).find('[name="delete_order"]').trigger("click");
			
			
			setTimeout( function(){				
			
			var preview_totals = $('#preview_totals').clone();
			$(preview_totals).attr("id", "xs_preview_totals");
			$('#xs_preview_totals').empty().append(preview_totals);
				}, 100 );	
			
		});
		
		
		
		
	});
	$('#change_user').click(function(){
		$('#orderDetails').modal('hide');
		$('#userLogin').modal('show').on('hidden.bs.modal', function(e){
			if(user.user_id != 0){
				initDetails();
			}
			$(this).unbind(e);
		});	
	
	});
	
	
		
		
		
	$('#user_Continue').click(function() {
		initDetails();
	
	});
	
	$('[name = "deliveryOption"]').change(function(e){
				var deliveryOption = $("[name=deliveryOption]:checked").val();
				if(deliveryOption=="pickup"){
					$('#delivery_address').hide();
					$('#driver_tip').hide();
					$('#delivery_address_new').hide();
					$('#delivery_address_buttons').hide();
					
				}
				if(deliveryOption=="delivery"){
					$('#delivery_address').show();
					$('#driver_tip').show();
					$('#delivery_address_buttons').show();
					$('#saveAddress').hide();
					$('#newAddress_Cancel').hide();
					
					
				}
	});
	
	
	$('[name = "payment_type"]').change(function(e){
				Order.paymentType = $("[name=payment_type]:checked").val();
			});
	
	$('[name = "deliveryTip"]').change(function(e){
		 
		var currentTip =  (parseFloat($("[name=deliveryTip]:checked").val())*parseFloat(Order.subTotal)).toFixed(2);
		Order.driverTip = currentTip; 
		
		currentTip = validatePrice(currentTip, currentTip);
		$('#deliveryTip').val(currentTip);
	
	});
	
	$('#change_payment').click(function(){
		$('#payment_method_div').fadeOut(300);
		$('#credit_div').show();
		$('#orderConfirm').find('.modal-content').scrollTop(10000);
		$('#offline_buttons').hide()
		
	
	});	
	
	
	$("body").scrollTop(0);		
});
	
	



	


