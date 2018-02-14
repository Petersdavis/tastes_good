<?php
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 

$secure = new Secure();
if(!$secure->user_id){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}



$a = new User();
$a->fromSession();

$menu = json_decode(getattribute("data"));
$rest_id = getattribute("rest_id");


$pass=0;
foreach($a->restaurants as $rest){
	if($rest->rest_id == $rest_id){
		$pass=1;
		$rest->menu = new Menu();
		break;
	}
}	

if ($pass == 0){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_rest";
	exit(json_encode($x));
}



		
foreach($menu->categories as $category){
	$b = new category();
	$b->id = $category->id;
	$b->category = $category->category;
	$b->extras = $category->extras;
	foreach($category->items as $item){
		//validate option price
		$pattern = '/[\d]+[.]{0,1}[\d]{0,2}/';
		preg_match($pattern, $item->price, $matches);
										
		$c = new item;
		$c->byAssignment($item->id, $item->product, $matches[0], $b->category, $item->description, $item->extras);
		array_push($b->items, $c);
	}
	$b->itemTotal = sizeof($b->items);	
	array_push($rest->menu->categories, $b);
}
		
foreach($menu->extras as $extra){
	$b= new extra();
	$b->id = $extra->id;
	$b->name = $extra->name;
	$b->question =$extra->question;
	$b->type = $extra->type;
	
	foreach($extra->options as $option){
		$c=new option;
		$c->name = $option->name;
		//validate option price
		$pattern = '/[\d]+[.]{0,1}[\d]{0,2}/';
		preg_match($pattern, $option->price, $matches);
		$c->price = $matches[0];
		$c->extras = $option->extras;
		array_push($b->options, $c);
	}
	array_push($rest->menu->extras, $b);
}

$rest->putSerial($rest_id);


$x=new stdClass();
$x->result = "success";

echo json_encode($x);
exit();

?>
