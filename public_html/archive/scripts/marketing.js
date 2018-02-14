Order = {"total":0, "items":{"biz_card":{"quantity":0, "unit_price":12.95 "tg_points":0, "subtotal":0}, "win_sticker":{"quantity":0, "unit_price":12.95 "tg_points":0, "subtotal":0}, "rest_id":rest_id}

$(function(){ 
	$('#biz_cards').change(function(){	
		var quantity = $('#biz_cards').val();	
		Order.items.biz_card.quantity = quantity
		var Order.items.biz_card.subtotal= = (Number(quantity) * Number(Order.items.biz_card.price)).toFixed(2);
		Order.total = (Number(Order.items.biz_card.subtotal)+Number(Order.win_sticker.subtotal)).toFixed(2);
		$('#biz_card_total').text("$"+ Order.items.biz_card.subtotal);
		$('#basic_total').text("$" + Order.total);
	});
	
	$('#window_sticker').change(function(){
		var quantity = $('#window_sticker').val()
		Order.win_sticker.tg_points = Number(quantity) * Number(10);
		Order.win_sticker.subtotal = Number(win_sticker) * Number(Order.items.win_sticker.price);	
		Order.total = (Number(Order.items.biz_card.subtotal)+Number(Order.win_sticker.subtotal)).toFixed(2); 
		$('#win_sticker_total').text( validatePrice(total, total));
		$('#basic_total').text("$" + Order.total);
		
	});
	
	$('#confirm_payment').click(function(){
		var formData = new FormData ();
		formData.append ('nonce', JSON.stringify(window.nonce));
		formData.append ('order', JSON.stringify(Order));
									
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			$('#save_schedule').removeClass("btn-danger");
			$('#save_schedule').addClass("btn-default");
			$('#lg_details').data('checked', true);
			var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
			checklist.market = 1;
			localStorage.setItem("TGchecklist", JSON.stringify(checklist));
		
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/marketing_payment.php', true);
		xhr.send(formData);	
			
	});
	
	$('#submit_basic').click(function(){
		$('#payment').modal('show');			
		braintree.setup(btToken, "custom", {
			id: "braintreeForm",
			paypal:{
				container: "paypal_container",
				paymentMethodNonceInputField: "payment_nonce",
				amount: Order.total,
				currency: "CAD",
				onSuccess:function(payload){
					nonce = {"nonce":payload, "type":"paypal"};
					$('#confirm_payment').prop("disabled", false);									
					
				},
				onCancelled:function(){
					window.nonce = {};
					
				}
			},
			hostedFields: {
			  number: {
				selector: "#card-number"
			  },
			  
			  expirationMonth: {
				selector: "#expiration-month"
			  },
			  expirationYear: {
				selector: "#expiration-year"
			  },
			  
			  styles: {
				  
				  ":focus": { color: "#333333" },
				  ".invalid": { color: "#FF0000" },
			  }
			},
			onReady:function(integration){
				checkout = integration;	
				$('#payment').on('hidden.bs.modal', function(e){
					checkout.teardown(function () {
						checkout = null;
						$('#paypal_prepend').show()
					});
					$(this).unbind(e);
				});		
				
			},
			onPaymentMethodReceived:function(payload){
				nonce = payload
				$('#confirm_payment').prop("disabled", false);
				return false;
			}
		}); 
	});	
		
		
		
});