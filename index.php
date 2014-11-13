<?php
	$page = file_get_contents("http://webdiplomacy.net/board.php?gameID=15540&viewArchive=Orders");
	$powers = array(1 => "England", "France", "Italy", "Germany", "Austria", "Russia", "Turkey");

	preg_match_all("/occupationBar(\d).*?width:(\d*)/", $page, $finalscs, PREG_SET_ORDER);
	$sccount = array_fill_keys($powers, 0);
	foreach($finalscs as $powersc)
		$sccount[$powers[$powersc[1]]] = round($powersc[2]*34/100);
	print_r($sccount);
	echo("<br>");

	preg_match("/Spring, 1901 .*\/p>/s", $page, $s01);
	preg_match_all("/The \w* at ([\w -\.]*\.)</", $s01[0], $moves);
	foreach($moves[1] as $unit)
	{
		if(preg_match("/(.*) (support) (move) to (.*) from (.*)\./", $unit, $unitarray) == 0)
			if(preg_match("/(.*) (support) (hold) to (.*)\./", $unit, $unitarray) == 0)
				if(preg_match("/(.*) (convoy) to (.*) from (.*)\./", $unit, $unitarray) == 0)
					if(preg_match("/(.*) (move) to (.*?)(via convoy)?\./", $unit, $unitarray) == 0)
						preg_match("/(.*) (hold)\./", $unit, $unitarray);
		$startspace = $unitarray[1];
		$moveaction = "-";
		switch($unitarray[2])
		{
			case "hold":
				$action = "h";
				$fullorder = "$startspace $action";
				break;
			case "move":
				$destination = $unitarray[3];
				$fullorder = "$startspace $moveaction $destination";
				break;
			case "support":
				$action = "s";
				if($unitarray[3] == "move")
				{
					$supported = $unitarray[5];
					$sdestination = $unitarray[4];
					$fullorder = "$startspace $action $supported $moveaction $sdestination";
				}
				else
				{
					$supported = $unitarray[4];
					$fullorder = "$startspace $action $supported";
				}
				break;
			case "convoy":
				$action = "c";
				$convoyed = $unitarray[4];
				$cdestination = $unitarray[3];
				$fullorder = "$startspace $action $convoyed $moveaction $cdestination";
				break;
		}
		echo("$fullorder<br>");
	}
?>
