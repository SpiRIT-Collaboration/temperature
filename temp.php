<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

$handle = fopen('UnitAsAd.map', 'r');
if (fgetc($handle) == '#')
  fgets($handle);

while (!feof($handle)) {
  $line = fgets($handle);
  $array = preg_split('/\s+/', $line);
  $map[$array[2]][$array[3]] = (string)$array[1];
}
fclose($handle);

define("TEXT_FILE", "../isobe/get/asad-monitoring.log");
define("LINES_COUNT", 28);

function read_file($file, $lines) {
    //global $fsize;
    $handle = fopen($file, "r");
    $linecounter = $lines;
    $pos = -2;
    $beginning = false;
    $text = array();
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if(fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true; 
                break; 
            }
            $t = fgetc($handle);
            $pos --;
        }
        $linecounter --;
        if ($beginning) {
            rewind($handle);
        }
        $text[$lines-$linecounter-1] = fgets($handle);
        if ($beginning) break;
    }
    fclose ($handle);
    return array_reverse($text);
}

$fsize = round(filesize(TEXT_FILE)/1024/1024,2);
$lines = read_file(TEXT_FILE, LINES_COUNT);
foreach ($lines as $line) {
  $dataArray = preg_split("/\s+/", $line);
  $coboID = explode(".", $dataArray[0]);
  $coboID = $coboID[3]%40;
  $asadID = substr($dataArray[1], 4);
  $tempValue = ($dataArray[7] + $dataArray[8])/2;
  $tempInt[$map[$coboID][$asadID]] = $dataArray[7];
  $tempExt[$map[$coboID][$asadID]] = $dataArray[8];
  if ($dataArray[7] > $dataArray[8]) $bigger = $dataArray[7];
  else                               $bigger = $dataArray[8];
  
  if ($bigger >= 40)                     $color[$map[$coboID][$asadID]] = "red";
  elseif ($bigger < 40 && $bigger >= 35) $color[$map[$coboID][$asadID]] = "yellow";
  elseif ($bigger < 35 && $bigger >= 30) $color[$map[$coboID][$asadID]] = "green";
  elseif ($bigger < 30)                  $color[$map[$coboID][$asadID]] = "#1BA8E1";

  $timestamp = $dataArray[2]." ".$dataArray[3];
}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset='utf-8'>
    <meta http-equiv='Refresh' content='5' url=''>
		<title>AsAd temperature monitor</title>

		<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script> -->
    <style>
      table {
        border: 1px solid black;
        padding: 0px;
        border-spacing: 0px;
      }
      tr {
        height: 40px;
      }
      td {
        border: 1px solid black;
        width: 200px;
        text-align: center;
      }
    </style>
	</head>
	<body>
    <h1>AsAd Temperature Monitor (<?php echo $timestamp; ?>)</h1>
    <table>
<?php
for ($uaRow = 11; $uaRow > -1; $uaRow--) {
  echo "<tr>";
  for ($uaCol = 0; $uaCol < 4; $uaCol++) {
    if ($uaRow < 10)
      $index = $uaCol."0".$uaRow;
    else
      $index = $uaCol.$uaRow;

    echo "<td style='background:".$color[$index].";'>Int: ".$tempInt[$index]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ext: ".$tempExt[$index]."</td>";
  }
  echo "</tr>";
}
?>
    </table>
	</body>
</html>
