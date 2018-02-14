


<ul id = "left_nav" class="nav menu list-group" >
	<a class="list-group-item" href="index.php"><svg class="glyph"><use xlink:href="#stroked-dashboard-dial"/></svg>Dashboard:</a>
	<a class="list-group-item" href="restaurant.php"><svg class="glyph"><use xlink:href="#stroked-gear"/></svg>Business Details:</a>
	<a class="list-group-item" href="orders.php"><svg class="glyph"><use xlink:href="#stroked-tag"/></svg>Account Summary:</a>
	<a class="list-group-item" href="community.php"><svg class="glyph"><use xlink:href="#stroked-heart"/></svg> My Community:</a>
	<a class="list-group-item" href="editMenu.php"><svg class="glyph"><use xlink:href="#stroked-bacon-burger"/></svg> Edit Menu: </a>
	<a class="list-group-item" href="coupons.php"><svg class="glyph"><use xlink:href="#stroked-eye"/></svg>Generate Coupons:</a>
	<a class="list-group-item" href="printer.php"><svg class="glyph"><use xlink:href="#stroked-printer"/></svg> Printer Set-Up:</a>
	
	<h3>Checklist </h3>
	<br><br>
	<div style="max-height: 400px;overflow: auto;">
	  	<ul id = "progress_checklist" class="list-group checked-list-box">
		  <a href= "#"> <li  id="lg_login" class="list-group-item">1. Login</li></a>
		  <a href= "restaurant.php"><li id="lg_details" class="list-group-item">2. Restaurant Details</li></a>
		  <a href= "editMenu.php"><li id="lg_menu" class="list-group-item">3. Create Menu</li></a>
		  <a href= "printer.php"><li id="lg_print" class="list-group-item">4. Install Print Software</li></a>
		  <a href= "coupons.php"><li  id="lg_coupon" class="list-group-item">5. Coupons (Optional)</li></a>
		  <a href= "#"><li  id="lg_launch" class="list-group-item">6. Launch!!</li></a>
		</ul>
	 
	</div>
	
	<h3>Reference:</h3>
	<a class="list-group-item disabled"  href="icons.php"><svg class="glyph"><use xlink:href="#stroked-star"></use></svg> Icons</a>
</ul>

<script>
$(function () {
    var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
    if(typeof(checklist)=="object" && checklist !==null){
    	
	if(checklist.login){$('#lg_login').data('checked', true);}
	if(checklist.details){$('#lg_details').data('checked', true);}	
	if(checklist.menu){	$('#lg_menu').data('checked', true);}		
	if(checklist.print){$('#lg_print').data('checked', true);}		
	if(checklist.coupon){$('#lg_coupon').data('checked', true);}	
	if(checklist.launch){$('#lg_launch').data('checked', true);}
	
	}else{
	//create the checklist object
		checklist = {"login":1,"details":0,"menu":0, "print":0, "coupon":0, "launch":0}
		window.localStorage.setItem("TGchecklist", JSON.stringify(checklist));
		$('#lg_login').data('checked', true);
	}
	
	$('.list-group.checked-list-box .list-group-item').each(function () {
        
        // Settings
        var $widget = $(this),
            $checkbox = $('<input type="checkbox" class="hidden" />'),
            color = ($widget.data('color') ? $widget.data('color') : "primary"),
            style = ($widget.data('style') == "button" ? "btn-" : "list-group-item-"),
            settings = {
                on: {
                    icon: 'glyphicon glyphicon-check'
                },
                off: {
                    icon: 'glyphicon glyphicon-unchecked'
                }
            };
            
        $widget.css('cursor', 'pointer')
        $widget.append($checkbox);

        // Event Handlers
        $widget.on('click', function (e) {
           
        });
        $checkbox.on('change', function () {
            updateDisplay();
        });
          

        // Actions
        function updateDisplay() {
            var isChecked = $checkbox.is(':checked');

            // Set the button's state
            $widget.data('state', (isChecked) ? "on" : "off");

            // Set the button's icon
            $widget.find('.state-icon')
                .removeClass()
                .addClass('state-icon ' + settings[$widget.data('state')].icon);

            // Update the button's color
            if (isChecked) {
                $widget.addClass(style + color + ' active');
            } else {
                $widget.removeClass(style + color + ' active');
            }
        }

        // Initialization
        function init() {
            
            if ($widget.data('checked') == true) {
                $checkbox.prop('checked', !$checkbox.is(':checked'));
            }
            
            updateDisplay();

            // Inject the icon if applicable
            if ($widget.find('.state-icon').length == 0) {
                $widget.prepend('<span class="state-icon ' + settings[$widget.data('state')].icon + '"></span>');
            }
        }
        init();
    });
    
    $('#get-checked-data').on('click', function(event) {
        event.preventDefault(); 
        var checkedItems = {}, counter = 0;
        $("#check-list-box li.active").each(function(idx, li) {
            checkedItems[counter] = $(li).text();
            counter++;
        });
        $('#display-json').html(JSON.stringify(checkedItems, null, '\t'));
    });
});
</script>