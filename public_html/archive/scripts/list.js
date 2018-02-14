var pagename = "list.php";
var sortingAddressId = 0;

function sortList(position){
	if(position.coords.accuracy < 800){
		var locate = {"lat":position.coords.latitude, "lng":position.coords.longitude}
		for(x=0; x<restaurants.length; ++x){
			restaurants[x].distance = calcDistance(locate, restaurants[x])
			restaurants[x].distance = Math.round(10*restaurants[x].distance)/10
			restaurants[x].charge = parseFloat(restaurants[x].delivery_base) + parseFloat(restaurants[x].delivery_rate * Math.floor(restaurants[x].distance/0.25))
			var prepend = "Distance: "
			if(position.coords.accuracy >250){
				prepend = "Aproximate Distance: "
			} 
			
			$('#'+restaurants[x].rest_id).find('[name="distance"]').text(prepend + restaurants[x].distance + " km").show();
			$('#'+restaurants[x].rest_id).find('[name="charge"]').text("Delivery Charge: $"+restaurants[x].charge).show();
		}
		restaurants.sort(function(a, b){return a.distance-b.distance});
		$('#set_address').text("Change Address")
			
		for(x=0;x<restaurants.length; ++x){
						
			$('#rest_list').append($('#'+restaurants[x].rest_id))
			
		}

		
	}else{
		locateFail();
	}
	
}

function locateFail(){
	$('#userLogin').modal('show');
}
function checkCoupon(coupon_code){
		var formData = new FormData ();
				
		formData.append ('coupon', coupon_code);
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			if(xhr.response==0){
				$('#coupon_error').show()
			}
			if(xhr.response>0){
				window.location.href = '/order.php?rest_id='+rest_id+'&coupon_code='+coupon_code;
			} 
		
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/checkCoupon.php', true);
		xhr.send(formData);	
}

function user_sortList(user){
	var position =  {coords:{latitude:user.addresses[0].lat, longitude:user.addresses[0].lng, accuracy:0}};
	sortList(position);
}

document.addEventListener("DOMContentLoaded", function(event) {
	$("#coupon_code_btn").click(function(){
		coupon_code = $('#coupon_code').val();
		checkCoupon(coupon_code);
			
	});
		
	if(user.user_id > 0 && user.addresses.length>0){
		user_sortList(user);	
						
		}else if(position.coords.latitude!=0){
			sortList(position);
		}
	
	$('#saveAddress').click(function(){
	 		 $('#chooseAddress').show()
			 geocodeAddress(geocoder);
	 });
	 
	$('#set_address').click(function(){
			if(user.user_id > 0 && user.addresses.length>0){
				$('#chooseAddress').modal('show');
				$('#delivery_address_new').hide();
				$('#saveAddress').hide();
				$('#newAddress_Cancel').hide();
				$('#newAddress').show();
				$('#order_AddressList').empty();
				
				
				for(x=0;x<user.addresses.length;++x){
					addAddress(user.addresses[x], $('#order_AddressList'));
				}
				if(sortingAddressId == 0){
					sortingAddressId = user.addresses[0].id
				}
					
				$('#address_radio_'+sortingAddressId).prop("checked", true);
				
				$('input[type=radio][name="Order_Address"]').change(function() {
						sortingAddressId = $('[name="Order_Address"]:checked').val()
						for(x=0;x<user.addresses.length;++x){
							if(user.addresses[x].id == sortingAddressId){
								sortingAddress = user.addresses[x]
								break;
							}
						}
						var position =  {coords:{latitude:sortingAddress.lat, longitude:sortingAddress.lng, accuracy:0}};
						sortList(position)
				});
				
				
			}else if(navigator.geolocation){
				navigator.geolocation.getCurrentPosition(sortList, locateFail);
			}else{
				locateFail();
			}
	});
	
	$('#show_coupons').click(function(){
			$('#rest_list').hide();
			$('#coup_list').show();
			$('#s_coupon_btn').hide();
			$('#h_coupon_btn').show();
	});		
	
	$('#hide_coupons').click(function(){
			$('#rest_list').show();
			$('#coup_list').hide();
			$('#s_coupon_btn').show();
			$('#h_coupon_btn').hide(); 
	})
		
		
});