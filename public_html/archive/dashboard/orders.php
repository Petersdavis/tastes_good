<?php 
include '../boilerplate.php';

if(!isset($_SESSION['pass'])||!$_SESSION['pass']){
	header('Location: ../');
}

checkProduction();?>
<?php include '../dbconnect.php'; 
html_Header("Account Summary");



//get orders

$rest_id = $_SESSION['rest_id'];

$rest=new Restaurant;
$rest->grabRest($rest_id);
include 'header.php';

$order_list=[];
$accountCredit = 0;


$sql = "SELECT  timezone FROM community WHERE name = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("s", $rest->community);
$stmt->execute();
$stmt->bind_result($timezone);
$stmt->fetch();
$stmt->close();
date_default_timezone_set($timezone);

$sql="SELECT link, order_id, order_time, payment_type, order_total, payment_fee, commission, tg_points FROM restaurant_orders WHERE rest_id = ?  ORDER BY order_time ASC";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->bind_result($link, $order_id, $order_time, $payment_type, $order_total, $payment_fee, $commission, $tg_points);
while($stmt->fetch()){
	$a=new Order();
	$a->order_id=$order_id;
	$a->total = $order_total;
	$a->timestamp=$order_time / 1000;
	$a->time = new DateTime();
	$a->time->setTimestamp($a->timestamp);
	$a->time = $a->time->format('Y-m-d H:i a');
	"../uploader/push_pdf.php?order_id=". $a->order_id;
	$a->paymentType= $payment_type;
	$a->payment_fee= $payment_fee;
	$a->commission= $commission;
	$a->tg_points= $tg_points;
	$a->link="<a href='../uploader/push_pdf.php?order_id=".$a->order_id."' download><img src='/images/pdf.png' style='width:20px'></img></a>";
	if($a->paymentType == "online"){
		$a->delta = $order_total - ($payment_fee + $commission);
	}else{
		$a->delta = -1 * $commission;
	}
	
	$accountCredit=$accountCredit + $a->delta;
	$a->accountCredit = $accountCredit;	
	
	array_push($order_list, $a);
}
$stmt->close();


$sql = "SELECT  SUM(order_subtotal), SUM(order_total), SUM(amount_paid), SUM(payment_fee), SUM(commission), SUM(tg_points) FROM restaurant_orders WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->bind_result($total_subtotal, $total_sales, $total_paid, $total_payment_fee, $total_commission, $total_tg_points);
$stmt->fetch();
$stmt->close();

$x = new stdClass();
$x->subtotal = $total_subtotal;
$x->total = $total_sales;
$x->paid =  $total_paid;
$x->payment = $total_payment_fee;
$x->commission = $total_commission;
$x->tg_points = $total_tg_points;


$new_rest = getattribute('new_rest');
if($new_rest){
	echo "<script>new_rest=1</script>";
}else{
	echo "<script>new_rest=0</script>";
}
?>


<!--Tables-->
<link href="https://cdn.jsdelivr.net/bootstrap.table/1.11.0/bootstrap-table.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>

<!--Icons-->
<script src="/dashboard/js/lumino.glyphs.js"></script>
<script>
data_list = <?php echo json_encode($order_list, JSON_UNESCAPED_SLASHES); ?>; 
totals = <?php echo json_encode($x); ?>;
clean_data = [];
</script>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->

<div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar">
	
	<?php include 'sidebar_content.php' ?>

</div><!--/.sidebar-->


<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">				
		
	<div class="row" style = "background-color:rgba(210,245,225,1);margin-left:5px;margin-right:5px;" >
		<div class="col-xs-12 ">
			<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Account Activity:<button onclick = "$('#tutorial').modal('show');" class = "btn btn-primary" style="float:right;font-size:25px" >?</button></h3>
			
			<h2 style = "margin-top:0; padding-top:10px; width:100%;font-size:20px;"> 
				<div class = "row" style="background-color:#f0ffff">
			
					<div class = "col-xs-12">
						
						<div class="row" >
							<div class="col-xs-12">
								<div class="panel-default">
									<div class="panel-body">
										<table id="rest_orders_table" class="compact table table-striped"></table>
									</div>
								</div>
							</div>
						</div><!--/.row-->	
						<div class = "row" >
							<div class = "col-xs-12">
								<ul class = "list-group" id = "totals">
									<li title="Gross Total of all sales" class= "list-group-item">Sales-Total: <span class = "badge" id="total_sales"><?php echo "$". $x->total; ?></span> </li>
									<li title="Total of sales paid online" class= "list-group-item">(Online Payments Received); <span class = "badge" id="total_paid"><?php echo "$". $x->paid; ?></span></li>
									<li title="2.5 percent of online sales" class= "list-group-item">Payment Processing Fees: <span class = "badge" id="total_paymentfee"><?php echo "$". $x->payment; ?></span> </li>
									<li title="Net Total of all sales (not including: taxes, tips or delivery charges)" class= "list-group-item">(Total Commissionable); <span class = "badge" id="total_commissionable"><?php echo "$". $x->subtotal; ?></span></li>
									<li title="3.5 percent of Comissionable" class= "list-group-item">Commissions: <span class = "badge" id="total_commission"><?php echo "$". $x->commission; ?></span> </li>
									<br>
									<li class= "list-group-item"><h3 style="margin-top:0;font-size:20px;width:100%;"> Account Balance:  <span class = "badge" id="account_balance" style="float:right;font-size:20px"><?php echo "$". $accountCredit; ?></span></h3></li>
									<br>
								</ul>
							
							</div>
						</div><!--/.row-->
					</div>
					
					<button id = "requestPayment" class = "btn btn-primary" style="float:right;font-size:20px; margin-right:25px;">Request Payment</button>
					
				</div>
			
			</h2>
		</div>
	</div><!--/.row-->
</div><!--/.main-->
	
<div class="modal fade" id="tutorial" tabindex="-1" role="dialog" style = "width:85%" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document" style = "width:85%">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Account Activity: </h3>
      	<button type="button" class="close" onclick="$('#tutorial').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
      			<h3  style="line-height:25px;font-size:22px;"> 1. Description: </h3>
      			<h2 style="line-height:25px;font-size:20px;">As your restaurant processes orders your account activity page will begin to look like this:</h2>
      			<img src="tutorial/accounts.jpg" style="width:100%" />
      			<h3 style="line-height:25px;font-size:22px;"> 2. Yellow Orders: </h3>
      			<h2  style="line-height:25px;font-size:20px;">These orders are paid in cash either in store or on delivery.  This means that you have received the total amount, but you owe us the commission fee</h2>
      			<h3 style="line-height:25px;font-size:22px;"> 3. Green Orders: </h3>
      			<h2  style="line-height:25px;font-size:20px;">These orders are paid online payments.  We will disburse the total amount to you, minus payment processing fees and outstanding commissions</h2>
      			<h3 style="line-height:25px;font-size:22px;"> 4. Our Math: </h3>
      			<h2  style="line-height:25px;font-size:20px;">We want to be as fair as possible in how we calculate our commission fees.  These fees are calculated based on the sub-total of each order.   This means that you pay zero commission on the tax, the delivery fees, or the driver tip.</h2>
      			<h2  style="line-height:25px;font-size:20px;">On the other hand we charge payment processing fees based on the total amount including tax, delivery charges, and driver tips </h2>
      			<h2  style="line-height:25px;font-size:20px;">These commission fees and payment processing fees are calculated at the bottom of the page. </h2>
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#tutorial').modal('hide')" type="button">Back</button>
      </div>    
    </div>
  </div>
</div>


<div class="modal fade" id="payment_success" tabindex="-1" role="dialog" style = "width:85%" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document" style = "width:85%">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Payment Successful:  </h3>
      	<button type="button" class="close" onclick="$('#payment_success').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<h2 style="line-height:25px;font-size:20px;"> A payment of <?php echo "$". $accountCredit; ?> has been sent to  <strong> <?php echo $rest->email; ?> </strong></h2>
      			
      			
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#payment_success').modal('hide')" type="button">Back</button>
      </div>    
    </div>
  </div>
</div>
<div id="loading_gif"  style = "border-width:0.8px;border-style:solid;display:none; position:fixed; height:150px; top:35%; width:50%; left:25%; border-radius:8px; box-shadow: 2px 2px 4px darkgrey;z-index:10000;background-color:white">
<img src="../images/loading.gif" style ="height:140px;margin:5px;"/>
<strong style="font-size:25px;"> Loading... </strong>
</div>


	<script>
	$(document).ready(function() {
    var x;
	for(x=0; x<data_list.length; ++x){
	clean_data.push([data_list[x].link, data_list[x].order_id, data_list[x].time, data_list[x].total, data_list[x].paymentType, data_list[x].payment_fee, data_list[x].commission, data_list[x].delta, data_list[x].accountCredit]);
		
	}
	
	
	$('#requestPayment').click(function(){
		
		$('#loading_gif').slideDown(200).delay(1000);
		$( "#loading_gif" ).queue(function() {
		  $( "#loading_gif" ).hide();	
		  $('#payment_success').modal('show');
		  $( this ).dequeue();
		});
		
		return;
	
		var xhr = new XMLHttpRequest();
		
		
		xhr.onload = function() {
			$('#loading_gif').hide();
			
			
				console.log(xhr.response);
				ret = JSON.parse(xhr.response)
				
				if(ret.result== "success"){
					
					$('#payout_success').delay(300).modal("show");
					
				}else{
					
					$('#payout_error').delay(300).modal("show");
				}
		};
	
	
		// Open the connection.
		xhr.open('POST', 'uploader/make_payout.php', true);
		xhr.send(formData);
	
	
	});
			
	$('#rest_orders_table').DataTable( {
        data: clean_data,
        "paging": false,
        "searching": false,
        "createdRow": function( row, data, dataIndex ) {
			if ( data[4] == "online" ) {
			  $(row).addClass( 'alert-success' );
			} else if (data[4] == "offline" ) {
			  $(row).addClass( 'alert-warning' );
			}
    	} ,
    	 "initComplete": function(settings, json) {
			$('#rest_orders_table_info').hide();
		  },
        columns: [
            { title: "pdf" },
            { title: "Id#" },
            { title: "Time" },
            { title: "Total" },
            { title: "Payment" },
            { title: "Online Fee" },
            { title: "Commission" },
            { title: "Diff" },
            { title: "Total" }
        ]
    } );
    
    } );
	
	</script>	
</body>

</html>