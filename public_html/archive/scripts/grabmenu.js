
jQuery.ajax = (function(_ajax){
    
    var protocol = location.protocol,
        hostname = location.hostname,
        exRegex = RegExp(protocol + '//' + hostname),
        YQL = 'http' + (/^https/.test(protocol)?'s':'') + '://query.yahooapis.com/v1/public/yql?callback=?',
        query = 'select * from html where url="{URL}" and xpath="*"';
    
    function isExternal(url) {
        return !exRegex.test(url) && /:\/\//.test(url);
    }
    
    return function(o) {
        
        var url = o.url;
        
        if ( /get/i.test(o.type) && !/json/i.test(o.dataType) && isExternal(url) ) {
            
            // Manipulate options so that JSONP-x request is made to YQL
            
            o.url = YQL;
            o.dataType = 'json';
            
            o.data = {
                q: query.replace(
                    '{URL}',
                    url + (o.data ?
                        (/\?/.test(url) ? '&' : '?') + jQuery.param(o.data)
                    : '')
                ),
                format: 'xml'
            };
            
            // Since it's a JSONP request
            // complete === success
            if (!o.success && o.complete) {
                o.success = o.complete;
                delete o.complete;
            }
            
            o.success = (function(_success){
                return function(data) {
                    
                    if (_success) {
                        // Fake XHR callback.
                        _success.call(this, {
                            responseText: (data.results[0] || '')
                                // YQL screws with <script>s
                                // Get rid of them
                                .replace(/<script[^>]+?\/>|<script(.|\s)*?\/script>/gi, '')
                        }, 'success');
                    }
                    
                };
            })(o.success);
            
        }
        
        return _ajax.apply(this, arguments);
        
    };
    
})(jQuery.ajax);


 


function addEventListeners(){	
	for (i=0; i<document.forms.length; ++i){
			//id of form
			form_id = document.forms[i].id;
			form = document.getElementById(form_id);
			form.addEventListener("submit", SubmitForm, false);
	}
}

function SubmitForm(event){

		event.preventDefault();
		
		form = event.target;
		form_id = event.target.id;
		form_type = form.name;
		
		
		var JUSTEATurl = document.getElementById('menu_url').value
		$.ajax({
					url: JUSTEATurl,
					type: 'GET',
					success: function(res) {
						
						response = res
						/*
						var pre = document.createElement('pre');
						pre.innerHTML = res;
						document.body.appendChild(pre);
			*/
					}
		});
		
		

		
		/*
		//categories JSON
		var categories = []
		var category = {}
		var item = {}
		
		for(i=0;i<window.CategoryTotal;++i){
			var items = []
			var id=i+1	
			var cat = document.getElementById("category["+id+"]");
			var cat_items =document.getElementById("category["+id+"]_items");
			var cat_name = document.getElementById("category["+id+"][category]").value;
			var cat_hierarchy = document.getElementById("category["+id+"][hierarchy]").value;
			
			
			for(j=0;j<cat_items.children.length;++j){
				const countj = j+1;
				var itm_product = document.getElementById("category["+id+"]item["+countj+"][product]").value
				var itm_price = document.getElementById("category["+id+"]item["+countj+"][price]").value
				var itm_desc = document.getElementById("category["+id+"]item["+countj+"][description]").value
				
				item = {'product':itm_product, 'price':itm_price, 'description':itm_desc};
				
				items.push(item);
			}
			
			category = {'category':cat_name, 'hierarchy':cat_hierarchy, 'items':items};
			
			categories.push(category);
		}
		
		var category_json = JSON.stringify(categories)
			
		
		var formData = new FormData ();
					
		formData.append ('categories', category_json);
		formData.append ('form_type', form_type);
		formData.append ('rest_id', window.rest_id);
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
			var pre = document.createElement('pre');
			pre.innerHTML = xhr.response;
			document.body.appendChild(pre);
			//get global variables
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'uploader.php', true);
		xhr.send(formData);
		
		//Action to the Different Forms
		
	   */
}
	
	
	
	


//stop forms from changing page
document.addEventListener("DOMContentLoaded", function(event) { 
			
		PlaceHolder();
		addEventListeners();
		//ConstructMenu(menu);
});	


	


