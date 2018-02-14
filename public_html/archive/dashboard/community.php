<?php include '../boilerplate.php'; 

if(!isset($_SESSION['pass'])||!$_SESSION['pass']){
	header('Location: ../');
}

checkProduction();
include '../dbconnect.php'; 
html_Header("Your Community"); 

$community = $_SESSION['community'];
$rest_id = $_SESSION['rest_id'];
$file = __FILE__;


$comm = new Community();

//get the community
$sql = "Select lat, lng, timezone, time_offset, count, launch_date, delivery_base, delivery_rate, status FROM community WHERE name = ?";
$stmt= $conn->prepare($sql);
$stmt->bind_param("s", $community);
$stmt->execute();
$stmt->bind_result($lat, $lng, $timezone, $time_offset, $count, $launch, $delivery_base, $delivery_rate, $status);
while($stmt->fetch()){
	$comm->name = $community;
	$comm->lat =$lat;
	$comm->lng = $lng;
	$comm->timezone = $timezone;
	$comm->time_offset = $time_offset;
	$comm->count = $count;
	if (!$launch){
		$comm->launch = "WAITING FOR RESTAURANTS";
	}else {
		$comm->launch = $launch;
	}
	$comm->delivery_base = $delivery_base;
	$comm->delivery_rate = $delivery_rate;
	$comm->status = $status;
}
$stmt->close();



//get the log
$community_logs = [];
class comm_log{
	public $id;
	public $title;
	public $content;
	public $type;
	public $rest_id;
	public $image;
	public $timestamp;
	public $date;
}


//get the Restaurants
$community_rest = [];

$sql = "Select rest_id, image, title, status, points, address FROM restaurants WHERE community = ? ORDER BY points DESC, title";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $community);
$stmt->execute();
$stmt->bind_result($rest_id, $image, $title, $status, $points, $address);
while($stmt->fetch()){
	$a = new restaurant();
	$a->rest_id = $rest_id;
	$a->image = $image;
	$a->title = $title;
	$a->status = $status;
	$a->points = $points;
	$a->address = $address;
	
	
	array_push($community_rest, $a);
}
$stmt->close();


$count_new = 0;
$count_test = 0;
$count_decline = 0;
$count_ready = 0;
$count_active = 0;
$sql = "Select status, Count(rest_id) FROM restaurants WHERE community = ? Group By status";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $community);
$stmt->execute();
$stmt->bind_result($status, $count);
while($stmt->fetch()){
  switch ($status) {
  	  case "NEW":
        $count_new = $count;
        break;
      case "TESTING":
        $count_test = $count;
        break;
      case "DECLINED":
        $count_decline = $count;
        break;
      case "READY":
        $count_ready = $count;
        break;
      case "ACTIVE":
        $count_active = $count;
        break;
  }
}
$stmt->close();

$participation = round(100*($count_test + $count_ready + $count_active)/(sizeof($community_rest)))  ;


$new_rest = getattribute('new_rest');
if($new_rest){
	echo "<script>new_rest=1</script>";
}else{
	echo "<script>new_rest=0</script>";
}

$rest = new Restaurant();
$rest->grabRest($rest_id);
include 'header.php';
?>


<!--Icons-->
<script src="js/lumino.glyphs.js"></script>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->

<div class="main sidebar col-sm-3 col-lg-2 sidebar" style="margin-left:10px;">
	<?php include 'sidebar_content.php' ?>
</div><!--/.sidebar-->
<div class = "row">	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">			
		<div class="row" style = "background-color:rgba(210,245,225,1);margin-left:5px;margin-right:5px;" >
			<div class="col-xs-12 ">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Welcome to <?php echo $comm->name; ?> <button onclick = "$('#tutorial').modal('show');" class = "btn btn-primary" style="float:right;font-size:25px" >?</button></h3>
				<h2 style = "margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;"> 
					<div class = "row" style="background-color:#f0ffff">
					
						<h2  style = "width:100%;text-align:center;font-size:25px;">Launch Date: <?php echo $comm->launch; ?></h2>
						<h2  style = "width:100%;text-align:center;font-size:25px;">Population: <?php echo sizeof($community_rest); ?> Restaurants</h2>
						<h2  style = "width:100%;text-align:center;font-size:25px;">Participation Rate: <?php echo $participation; ?> %</h2>
						<h2  style = "width:100%;text-align:center;font-size:25px;">Target Participation: 60%</h2>
					</div>
				</h2>
			</div>
		</div>	
	</div>
</div>


<div class = "row" >	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">	
			<div class="row" style = "background-color:rgba(210,245,225,1);margin-top:15px;margin-left:5px;margin-right:5px;" >
			<div class="col-xs-12 ">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Community Members</h3>
					<h2 style = "margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;"> 
					<div class = "row" style="background-color:#f0ffff">
						
						<div class="panel-default chat">
							<div class="panel-body">
									<ul class = "list-group">
										<?php $rank = 1;  
										foreach($community_rest as $rest){ ?>
																			
										<li class="list-group-item">
											<div class = "row">
												<div class = "col-xs-1">
													<h3 style="color:black;"><?php echo $rank . ".";?></h3>
												</div>
												<div class = "col-xs-1 no-padding">
													<h3><?php echo $rest->points;?><span class="glyphicon glyphicon-star"></span></h3>
												</div>
												<div class =  "col-xs-3">
													<img src="<?php echo $rest->image; ?>" width="60" height="60"   class="image img-thumbnail" />
												</div>
												<div class = "col-xs-7">
													<h2 style = "font-size:20px; margin-bottom:0; margin-top:0;" > <?php echo $rest->title; ?> </h2>
													<small class="text-muted" style="margin-right:20px;"> <?php echo $rest->address;?> </small>
													<?php 
													switch ($rest->status) {
															case "DECLINED":
																$bgcolor = "black";
																break;
															case "TESTING":
																$bgcolor = "info";
																break;
															case "READY":
																$bgcolor = "primary";
																break;
															case "OPEN":
																$bgcolor = "success";
																break;
															case "CLOSED":
																$bgcolor = "warning";
																break;
															
															default:
																$bgcolor = "grey";
													}
													?>
													<div class="bg-<?php echo $bgcolor;?>" style="width:100%; height:20;">
															<?php echo $rest->status; ?> 
														
													</div>
													
												</div>
												
											
											</div>
										
										</li>
										
										<?php $rank+=1; } ?>
									</ul>
								</div>
							</div>
							
						</div>
						
						
						</h2>
					</div>
				</div>
		</div>
	</div>
</div>

<div class="modal fade" id="tutorial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Your Community: </h3>
      	<button type="button" class="close" onclick="$('#tutorial').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
      			<h3  style="line-height:25px;font-size:22px;"> 1. Description: </h3>
      			<h2 style="line-height:25px;font-size:20px;"> This page allows you to see the participation of other restaurants in your community</h2>
      			<h3 style="line-height:25px;font-size:22px;"> 2. Launch Date: </h3>
      			<h2  style="line-height:25px;font-size:20px;">Once we hit the target participation rates in this community we will schedule a launch date.</h2>
      			<h2  style="line-height:25px;font-size:20px;">Typically we give restaurants 2 weeks to prepare their menus and coupons before launching</h2>
      			<h2  style="line-height:25px;font-size:20px;">When the date is determined we will send out an email informing you of the launch and encouraging you to purchase marketting materials</h2>
      			<h3 style="line-height:25px;font-size:22px;"> 3. Community Members: </h3>
      			<h2  style="line-height:25px;font-size:20px;">As you can see we share some information about each of our restaurant partners on the Community page.</h2>
      			<h2  style="line-height:25px;font-size:20px;">This information lets you see how well local businesses are doing at promoting their menus online</h2>
      			
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#tutorial').modal('hide')" type="button">Okay</button>
      </div>    
    </div>
  </div>
</div>

</body>

</html>
