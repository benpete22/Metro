<!-- Latest compiled and minified CSS -->
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>


<style>
#main {
  width: 200px;
  margin: auto;
  text-align: center;
}
.time{
  margin: 0px;
}
.direction{
	margin:0;
	border-top:false;
	padding: 0px;
}
.bottomfoot{
	border-bottom:1px solid #ddd;
}
h3.direction{
	margin-top:10px;
	margin-bottom:10px
}
h2.direction{
	margin-top:10px;
	margin-bottom:10px
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?PHP
//Mack's Distance calculator
function calcDist($x1, $y1, $x2, $y2){
 $dist = sqrt(pow(($x2 - $x1), 2) + pow(($y2 - $y1), 2));
 return $dist;
};
//gets the coordinates from the URL
$cx = $_GET['cx'];
$cy = $_GET['cy'];
//gets the bus variable, I use this later to determine if you want to show bus routes or GreenLine.
$bus = $_GET['bus'];

//sets some variabls depending on bus or greenline
//this is creating the url eg. http://svc.metrotransit.org/nextrip/87/1/RAST?format=json

if ($bus == "True"){
$direction1 = "North";
$direction2 = "South";
$routeNum1 = "/87/4/";
$routeNum2 = "/87/1/";
$busGET = "&bus=True";
}else{
$direction1 = "East";
$direction2 = "West";
$routeNum1 = "/902/2/";
$routeNum2 = "/902/3/";
};



//checks if there are coordinates set
if(isset($_GET['cx']) == false){
	// if coordinates are not set it will use javascript/HTML 5 to find the coordinates and redirect the browser with them in the query string
  echo '<script>
    function getLocation() {
      if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(showPosition);
		  
      } else {
          x.innerHTML = "Geolocation is not supported by this browser.";
      }
    }
    function showPosition(position) {
      window.location.href = "http://narwhy.pw/metro/index.php?cx="+position.coords.latitude+"&cy="+ position.coords.longitude'.$busGET.';
    }
	getLocation();
	</script>';
};

//loads the CSV file depending if Bus or GreenLine
if ($bus == "True"){
$csv = array_map('str_getcsv', file('87Stations.csv'));	
}else{
$csv = array_map('str_getcsv', file('GreenLineStations.csv'));
}

//sets $dist to a really high number, the loop will then check to see which location has the lowest distance by compairing it to $dist 
$dist = 99999999999999;


//Loop that checkes the distance between current location and all GreenLineStations
foreach ($csv as $Station) {
    $distance = (calcDist($cx, $cy, $Station[0], $Station[1]));
 
	if ($distance < $dist){
		//if the current station has a lowwer distance it will rewrite $dist, $stationID and $stationName until the lowest distance is found.
		$dist = $distance;
		$stationID = $Station[2];
		$stationName = $Station[3];
	};
  
};




//function that requests whatever url is passed to it using cURL and returns the json encoded response
function getrequest($url){
	// gets cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url,
		CURLOPT_USERAGENT => 'BenGreenlineApp'
	));
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	// Close request to clear up some resources
	curl_close($curl);
	//returns the json encoded response
	return json_decode($resp);
};
	






	
	
	
?>	

	
	<script>
	//javascript that checks the location and refreshes the window every 30 seconds	
	function getLocation() {
      if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(showPosition);
		  
      } else {
          x.innerHTML = "Geolocation is not supported by this browser.";
      }
    }
    function showPosition(position) {
      window.location.href = "http://narwhy.pw/metro/index.php?cx="+position.coords.latitude+"&cy="+ position.coords.longitude<?PHP echo $busGET; ?>;
    }
	setTimeout("getLocation();", 30000);
	</script>
	

	

	<br>
	<div class=container-fluid>
		<div id=main class="panel panel-default">
			<div class="panel-heading"><h2 class="direction">
				<?PHP echo $stationName; ?>
				</h2>
			</div>
			<div class='panel-body direction'><h3  class="direction"> <?PHP echo $direction1; ?> </h3>
				<?PHP
				//Sets the URL
				$requrl ='http://svc.metrotransit.org/nextrip'.$routeNum1.$stationID.'?format=json';
				//requests the URL
				$output = getrequest($requrl); ?>
				<h4 class="direction"><div class='time panel-footer'> <?PHP echo $output[0]->DepartureText ?> </div>
				<div class='time panel-footer bottomfoot'> <?PHP echo $output[1]->DepartureText ?></div></h4>
			</div> 
			<div class='panel-body direction'><h3  class="direction"> <?PHP echo $direction2; ?></h3>
				<?PHP
				//Sets the URL
				$requrl ='http://svc.metrotransit.org/nextrip'.$routeNum2.$stationID.'?format=json';
				//requests the URL
				$output = getrequest($requrl); ?>
				<h4 class="direction"><div class='time panel-footer'><?PHP echo $output[0]->DepartureText ?></div>
				<div class='time panel-footer bottomfoot'><?PHP echo $output[1]->DepartureText ?></div></h4>
				
			</div>
		</div>	
	</div>

	
	