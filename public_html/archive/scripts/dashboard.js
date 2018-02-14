var hostname = location.hostname

function OpenClose(closed){
	var formData = new FormData ();
	formData.append ('close', closed);
	
									
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);		
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/open_close.php', true);
		xhr.send(formData);	
}
	

$(function(){ 
	
	$('#submit_activation').click(function(){
		var formData = new FormData ();
		formData.append ('rest_id', rest_id)
										
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			
			result = JSON.parse(xhr.response);
			if(result.result == "success"){
				
			
				$('#activate_modal').modal('hide');
				$('#pay_for_marketing').delay(250).modal('show')
								
						
				//setup braintree
				braintree.setup(btToken, "custom", {
					id: "braintreeForm",
					paypal:{
						container: "paypal_container",
						paymentMethodNonceInputField: "payment_nonce",
						amount: 29.95,
						currency: "CAD",
						onSuccess:function(payload){
							window.nonce = {"nonce":payload, "type":"paypal"};
							$('#credit_div').hide();
							$('#payment_method_div').show();
							$('#make_payment').removeClass('disabled').attr('disabled', false);
													
							
							
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
						$('#pay_for_marketing').on('hidden.bs.modal', function(e){
							checkout.teardown(function () {
								checkout = null;
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
									
								
						 
						return false;
				
				  }
			}); 
				
			
			}
			
			setTimeout(function(){ $('#refund_order_received').modal("show"); }, 1000);
			
		};
		
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/activate_restaurant.php', true);
		xhr.send(formData);		
	  
	});
	
	$('#make_payment').click(function(){
		var formData = new FormData ();
		
		var choice = 1;
		formData.append ('payment_choice', choice);
	
									
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);		
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/restaurant_payment.php', true);
		xhr.send(formData);	
	
	});
	
	
	$('#no_payment').click(function(){
		var formData = new FormData ();
		var choice = 0;
		formData.append ('payment_choice', choice);
	
	
									
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);		
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/restaurant_payment.php', true);
		xhr.send(formData);	
	
	});
	
	
	
	$('[name = "activation_checklist"]').change(function(e){
		if($('#chk1').prop("checked")&& $('#chk2').prop("checked") && $('#chk3').prop("checked") && $('#chk4').prop("checked") && $('#chk5').prop("checked")){
			$('#submit_activation').removeClass('disabled').attr('disabled', false)
		}else{
			$('#submit_activation').addClass('disabled').attr('disabled', true)
		}
	});
	
	if(new_rest==1){
		$('#tutorial_new').modal('show');
	}
        if(confirm_order==1){
             $('#order_confirmed').modal('show');
        }else if(confirm_order== 0){
             $('#order_rejected').modal('show');
        }


        $('[name="confirm_order"]').click(function(event){
            var order_id = $(event.target).data("order_id");
            window.location= "../uploader/order_confirm.php?orderId="+order_id;

        });
        $('[name="reject_order"]').click(function(event){
            var order_id = $(event.target).data("order_id");
            window.location= "../uploader/order_reject.php?orderId="+order_id;

        });

	$('[name="store_credit"]').click(function(event){
			var order_id = $(event.target).data("order_id");	
			var x;
			var order;
			
			for(x=0; x<orders.length; ++x){
				if(order_id == orders[x].order_id){
					order = orders[x];
				}
			}
			
			$('#credit').modal('show');
			$('#credit_id').text(order.order_id);
			$('#credit_name').text(order.user.fname + " " +order.user.lname);
			$('#credit_phone').text(order.user.phone);
			$('#credit_total').text(order.total);

			
			
			$('#submit_credit').unbind("click").on('click', {order_id:order.order_id}, function(event){
				
				
				var credit = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#credit_amount').val())[0];
				var message = $('#credit_message').val();
				
				if(credit>0){
				var formData = new FormData ();
				formData.append ('order_id', event.data.order_id)
				formData.append ('credit', credit)
				formData.append ('message', message)
										
				var xhr = new XMLHttpRequest();
				
				xhr.onload = function() {
					console.log(xhr.response);
					setTimeout(function(){ $('#credit_order_received').modal("show"); }, 1000);
					
				};
				
				// Open the connection.
				xhr.open('POST', 'https://'+ hostname + '/uploader/credit_order.php', true);
				xhr.send(formData);		
				$('#credit').modal("hide");	
				}
					
			});
			
		});
	
	$('[name="refund_order"]').click(function(event){
			var order_id = $(event.target).data("order_id");
			var x;
			var order;
			
			for(x=0; x<orders.length; ++x){
				if(order_id == orders[x].order_id){
					order = orders[x];
				}
			}
			
			$('#refund').modal('show');
			$('#refund_id').text(order.order_id);
			$('#refund_name').text(order.user.fname + " " +order.user.lname);
			$('#refund_phone').text(order.user.phone);
			$('#refund_total').text(order.total);
			$('#max_refund').text(order.total);
			
			
			
			$('#submit_refund').unbind("click").on('click', {order_id:order.order_id}, function(event){
				
				var max = $('#max_refund').text();
				var refund = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#refund_amount').val())[0];
				var reason = $('#refund_reason').val();
				
				if(refund>0){
				var formData = new FormData ();
				formData.append ('order_id', event.data.order_id)
				formData.append ('refund', refund)
				formData.append ('reason', reason)
										
				var xhr = new XMLHttpRequest();
				
				xhr.onload = function() {
					console.log(xhr.response);
					setTimeout(function(){ $('#refund_order_received').modal("show"); }, 1000);
					
				};
				
				// Open the connection.
				xhr.open('POST', 'https://'+ hostname + '/uploader/refund_order.php', true);
				xhr.send(formData);		
				$('#refund').modal("hide");	
				}
					
			});
	})
		
	$('[name="cancel_order"]').click(function(event){
			
			var order_id = $(event.target).data("order_id");
			var x;
			var order;
			
			for(x=0; x<orders.length; ++x){
				if(order_id == orders[x].order_id){
					order = orders[x];
				}
			}
			
			$('#cancel_order').modal('show');
			$('#cancel_id').text(order.order_id);
			$('#cancel_name').text(order.user.fname + " " +order.user.lname);
			$('#cancel_phone').text(order.user.phone);
			$('#cancel_total').text(order.total);
			$('#submit_cancel').unbind("click").on('click', {order_id:order.order_id}, function(event){
				var formData = new FormData ();
				formData.append ('order_id', event.data.order_id)
				formData.append ('reason', $('#cancel_reason').val())
										
				var xhr = new XMLHttpRequest();
				
				xhr.onload = function() {
					setTimeout(function(){ $('#cancel_order_received').modal("show"); }, 1000);
					console.log(xhr.response);		
				};
				
				// Open the connection.
				xhr.open('POST', 'https://'+ hostname + '/uploader/cancel_order.php', true);
				xhr.send(formData);		
				$('#cancel_order').modal("hide");	
					
			});
			
			
	});
		
		
	if($('#close_switch').is(':checked')){
			$('#switch_status').text("CLOSED")
		}else{
			$('#switch_status').text("OPEN")
			
	}	
		
		
	$('#close_switch').change(function(){
		var closed;
		if($('#close_switch').is(':checked')){
			closed = 1
			OpenClose(closed);
			
			$('#switch_status').text("CLOSED")
		}else{
			closed = 0
			OpenClose(closed);
			
			$('#switch_status').text("OPEN")
			
		}
			
			
	});
		
		
});