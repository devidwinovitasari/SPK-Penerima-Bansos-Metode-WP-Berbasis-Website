<?php
	session_start();
	include('configdb.php');
?>
<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
	<style>
        .a {
            background-color: #F5F5F5;
            padding: 20px;
            border-radius: 5px;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 400px;
            width: 80%;
        }
        canvas {
            display: block;
            width: 100%;
            height: auto;
        }
        .chartjs-tooltip {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px;
            border-radius: 5px;
            position: absolute;
            z-index: 100;
            pointer-events: none;
            display: none;
        }
        .chartjs-tooltip.active {
            display: block;
        }
    </style>
    <title><?php echo $_SESSION['judul']." - ".$_SESSION['by'];?></title>

    <!-- Bootstrap core CSS -->
    <!--link href="ui/css/bootstrap.css" rel="stylesheet"-->
	<link href="ui/css/cerulean.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="ui/css/jumbotron.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./index_files/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

      <!-- Static navbar -->
      <nav class="navbar navbar-default">
        <div class="container">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><b><?php echo $_SESSION['judul'];?></b></a>
          </div>
          <div id="navbar" class="navbar-collapse collapse pull-right">
            <ul class="nav navbar-nav">
              <li><a href="index.php">Home</a></li>
              <li><a href="kriteria.php">Data Kriteria</a></li>
              <li><a href="alternatif.php">Data Alternatif</a></li>
			  <li class="active"><a href="#">Analisa</a></li>
              <li><a href="perhitungan.php">Perhitungan</a></li>
			</ul>
          </div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->
      </nav>
		<div class="container">
      <!-- Main component for a primary marketing message or call to action -->
      <div class="panel panel-primary">
		  <!-- Default panel contents -->
		  <div class="panel-heading"><b>Analisa</b></div>
		  <div class="panel-body">
			<div class="a">
				<canvas id="canvas"></canvas>
			</div>
			<br />
			<center>
				<?php

					$alt = get_alternatif();
					$alt_name = get_alt_name();
					end($alt_name);
					$arl2 = key($alt_name)+1; //new
					$kep = get_kepentingan();
					$cb = get_costbenefit();
					$k = jml_kriteria();
					$a = jml_alternatif();
					$tkep = 0;
					$tbkep = 0;

						for($i=0;$i<$k;$i++){
							$tkep = $tkep + $kep[$i];
						}
						for($i=0;$i<$k;$i++){
							$bkep[$i] = ($kep[$i]/$tkep);
							$tbkep = $tbkep + $bkep[$i];
						}
						for ($i = 0; $i < $k; $i++) {
							$cb = get_costbenefit(); // Assuming this function retrieves cost/benefit information
							if ($cb[$i] == "Benefit") {
							  $pangkat[$i] = $bkep[$i];
							} else {
							  $pangkat[$i] = (-1) * $bkep[$i];
							}
						  }
						for($i = 0; $i < $a; $i++){
							for($j = 0; $j < $k; $j++){
								if (isset($alt[$i][$j]) && isset($pangkat[$j])) {
									$s[$i][$j] = pow(($alt[$i][$j]), $pangkat[$j]);
								} else {
									// Tangani kasus di mana indeks tidak ada dalam array
									$s[$i][$j] = 1; // Atau nilai default yang sesuai
								}
							}
							// Asumsikan bahwa array $s[$i] sudah ada dan terdefinisi hingga indeks $k
							$ss[$i] = isset($s[$i][0]) ? $s[$i][0] : 1;
							for ($n = 1; $n < $k; $n++) {
								$ss[$i] *= isset($s[$i][$n]) ? $s[$i][$n] : 1;
							}
						}
						
					// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> vektor S <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< //
					echo "<b>Hasil Akhir</b></br>";
					echo "<table class='table table-striped table-bordered table-hover'>";
					echo "<thead><tr><th>Alternatif</th><th>V</th></tr></thead>";
					$total = 0;
					for($i=0;$i<$a;$i++){
						$total = $total + $ss[$i];
					}
					for($i=0;$i<$a;$i++){
						echo "<tr><td><b>".$alt_name[$i]."</b></td>";
						$v[$i] = round($ss[$i]/$total,6);
						echo "<td>".$v[$i]."</td></tr>";
					}
					echo "</table><hr>";
					// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> vektor S <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< //
					uasort($v,'cmp');

										function jml_kriteria(){
											include 'configdb.php';
											$kriteria = $mysqli->query("select * from kriteria");
											return $kriteria->num_rows;
										}

										function jml_alternatif(){
											include 'configdb.php';
											$alternatif = $mysqli->query("select * from alternatif");
											return $alternatif->num_rows;
										}

										function get_kepentingan(){
											include 'configdb.php';
											$kepentingan = $mysqli->query("select * from kriteria");
											if(!$kepentingan){
												echo $mysqli->connect_errno." - ".$mysqli->connect_error;
												exit();
											}
											$kep = array(); // Pastikan $kep adalah array kosong pada awalnya
											while ($row = $kepentingan->fetch_assoc()) {
												$kep[] = $row["kepentingan"]; // Gunakan array push untuk menambahkan nilai ke array
											}
											return $kep;
										}
										

										function get_costbenefit(){
											include 'configdb.php';
											$costbenefit = $mysqli->query("select * from kriteria");
											if(!$costbenefit){
												echo $mysqli->connect_errno." - ".$mysqli->connect_error;
												exit();
											}
											$i=0;
											while ($row = $costbenefit->fetch_assoc()) {
												@$cb[$i] = $row["cost_benefit"];
												$i++;
											}
											return $cb;
										}

										function get_alt_name(){
											include 'configdb.php';
											$alternatif = $mysqli->query("select * from alternatif");
											if(!$alternatif){
												echo $mysqli->connect_errno." - ".$mysqli->connect_error;
												exit();
											}
											$i=0;
											while ($row = $alternatif->fetch_assoc()) {
												@$alt[$i] = $row["alternatif"];
												$i++;
											}
											return $alt;
										}

										function get_alternatif(){
											include 'configdb.php';
											$alternatif = $mysqli->query("select * from alternatif");
											if(!$alternatif){
												echo $mysqli->connect_errno." - ".$mysqli->connect_error;
												exit();
											}
											$i=0;
											while ($row = $alternatif->fetch_assoc()) {
												@$alt[$i][0] = $row["k1"];
												@$alt[$i][1] = $row["k2"];
												@$alt[$i][2] = $row["k3"];
												@$alt[$i][3] = $row["k4"];
												@$alt[$i][4] = $row["k5"];
												$i++;
											}
											return $alt;
										}

										function cmp($a, $b){
											if ($a == $b) {
												return 0;
											}
											return ($a < $b) ? -1 : 1;
										}

										function print_ar(array $x){	//just for print array
											echo "<pre>";
											print_r($x);
											echo "</pre></br>";
										}
				?>
			</center>
		  </div>
		  <div class="panel-footer text-primary text-right"><b><?php echo $_SESSION['by'];?></b><div class="pull-right"></div></div>
		</div>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="ui/js/jquery-1.10.2.min.js"></script>
	<script src="ui/js/bootstrap.min.js"></script>
	<script src="ui/js/bootswatch.js"></script>
	<script src="ui/js/Chart.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="ui/js/ie10-viewport-bug-workaround.js"></script>
	<!-- chart -->
	<script>
	var randomScalingFactor = function(){ return Math.round(Math.random()*100)};

	var barChartData = {
		labels : [
			<?php
				for($i=0;$i<$a;$i++){
					echo '"'.$alt_name[$i].'",';
				}
			?>
		],
		datasets : [
			{
				fillColor : "rgba(0,0,255)",
				strokeColor : "rgba(220,220,220,0.8)",
				highlightFill: "rgba(0,128,255,0.75)",
				highlightStroke: "rgba(220,220,220,1)",
				data : [
					<?php
						for($i=0;$i<$a;$i++){
							echo $v[$i].',';
						}
					?>
				]
			},
			/*{
				fillColor : "rgba(151,187,205,0.5)",
				strokeColor : "rgba(151,187,205,0.8)",
				highlightFill : "rgba(151,187,205,0.75)",
				highlightStroke : "rgba(151,187,205,1)",
				data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
			}*/
		]

	}
	window.onload = function(){
		var ctx = document.getElementById("canvas").getContext("2d");
		window.myBar = new Chart(ctx).Bar(barChartData, {
			responsive : true
		});
	}

	</script>
</body></html>
