

function checkExtra(extra){
	foundExtra = 0
	theExtra = {}
	theExtra.type = "1"
	theExtra.options = [];
	theExtra.options = buildExtra(extra);
	
	
	for(b=0;b<myMenu.extras.length;++b){
		outer_loop:
		if(myMenu.extras[b].options.length == theExtra.options.length){
		
			for(c=0;c<myMenu.extras[b].options.length;++c){
				if(myMenu.extras[b].options[c].id !== theExtra.options[c].id){
				 break;
				}
				if(c == myMenu.extras[b].options.length - 1){
					theExtra = myMenu.extras[b];
					foundExtra = 1
					break outer_loop;
				}
			}
		
		}
	
	}
	
	if(foundExtra == 1){
	 return theExtra.id;
	}else{
	 
	
	 
	 
	 theExtra.id = myMenu.extras.length + 1
	 theExtra.name = "Extra_" + theExtra.id
	 
	 myMenu.extras.push(theExtra);
	 	
	 
	 return theExtra.id ;
	
	}
	
}

function buildOptions(extra){
	options = []
	
	for(d=0;d<extra.length;++d){
	 
		 for(e=0;e<Menu.accessories.length;++e){
			 if(Menu.accessories[e].Id = extra[d]){
			 option = {}
			 option.id = extra[d] 
			 option.name = Menu.accessories[e].Name
			 option.price = Menu.accessories[e].Price
			 option.extras = []
			 options.push(option);
			 break;
			
			 }
		
		 }
	}
	 
	return options; 
}




function buildExtra(extra){
	options = []
	for(d=0;d<extra.length;++d){
	 option = {}
	 option.id = extra[d].Id 
	 option.name = extra[d].Name
	 option.price = extra[d].Price
	 option.id = extra[d].Id
	 option.extras = []
	 options.push(option);
	  
	
	}
	
	return options;
}

function checkOption (extra){
	foundExtra = 0
	theExtra = {}
	
	 
	 theExtra.type = "2"
	 theExtra.options = [];
	 theExtra.options = buildOptions(extra);
	
	for(b=0;b<myMenu.extras.length;++b){
		outer_loop:
		if(myMenu.extras[b].options.length == theExtra.options.length){
		
			for(c=0;c<myMenu.extras[b].options.length;++c){
				if(myMenu.extras[b].options[c].id !== theExtra.options[c].id){
				 	break;
				}
				if(c == myMenu.extras[b].options.length - 1){
					theExtra = myMenu.extras[b];
					foundExtra = 1
					break outer_loop;
				}
			}
		
		}
	
	}
	
	if(foundExtra == 1){
	 return theExtra.id;
	}else{
	 
	 theExtra.id = myMenu.extras.length + 1
	 theExtra.name = "Extra_" + theExtra.id
	 myMenu.extras.push(theExtra)
	
	 return theExtra.id 
	
	}


}


function fetchItem(Id){
  var item = {}
  for(b=0; b<Menu.products.length;++b){
	if(Menu.products[b].Id == Id){
	item.extras = [];
	item.product = Menu.products[b].Name
	item.price = Menu.products[b].Price
	item.description = Menu.products[b].Desc
	if(Menu.products[b].Syn.length >0 ){
	  item.product =item.product + ": " +Menu.products[b].Syn
	}
	break;
	}
  }
  return item;

}



function scrapeMenu(){
var Cat, category, Itm, item, extraId;

myMenu = {}
myMenu.categories = []
myMenu.extras = []

	for(x=0;x<Menu.Categories.length;++x){
		Cat = Menu.Categories[x];
		category = {}
		category.category = Cat.name
		category.items = []
	
		for(y=0;y<Cat.Items.length;++y){
			Itm = Cat.Items[y];
		
		
			for(z=0;z<Itm.Products.length; ++z){
				Prod = Itm.Products[z];
				
				item = fetchItem(Prod.Id)
				item.category = Cat.Name;
			
			
				if(typeof(Prod.Accs)=="object"){
					a=1;
					while(typeof(Prod.Accs[a])=="object"){
						extraId = checkExtra(Prod.Accs[a]);
						item.extras.push(extraId);
						++a
					}
				}
				if(typeof(Prod.Opts)=="object"){
				
				extraId = checkOption(Prod.Opts);
				item.extras.push(extraId);
				
				}
				category.items.push(item);
				
			
			}
		
		
		}
	   myMenu.categories.push(category);
	}

}

function broadcast(){
	
	var xhr = new XMLHttpRequest();
	
	var formData = new FormData ();
	formData.append ('menu', JSON.stringify(myMenu));
	formData.append ('rest_id', rest_id);
	formData.append ('auth_code', auth_code);
	
	xhr.onload = function() {
		console.log(xhr.response); 	   		
		
	};  
		  
			
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/uploader/menu2.php', true);
	xhr.send();	
		
}



//Get Menu

Menu = $(".menu").data();
Menu = Menu.menu.Menu;

scrapeMenu();
