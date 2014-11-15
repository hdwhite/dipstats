<?php
	$page = file_get_contents("http://webdiplomacy.net/board.php?gameID=15540&viewArchive=Orders");
	$powers = array(1 => "England", "France", "Italy", "Germany", "Austria", "Russia", "Turkey");
	$scowners = array("Liverpool"		=> $powers[1],
					  "London"			=> $powers[1],
					  "Edinburgh"		=> $powers[1],
					  "Marseilles"		=> $powers[2],
					  "Paris"			=> $powers[2],
					  "Brest"			=> $powers[2],
					  "Venice"			=> $powers[3],
					  "Rome"			=> $powers[3],
					  "Naples"			=> $powers[3],
					  "Munich"			=> $powers[4],
					  "Berlin"			=> $powers[4],
					  "Kiel"			=> $powers[4],
					  "Trieste"			=> $powers[5],
					  "Budapest"		=> $powers[5],
					  "Vienna"			=> $powers[5],
					  "Ankara"			=> $powers[6],
					  "Smyrna"			=> $powers[6],
					  "Constantinople"	=> $powers[6],
					  "St. Petersburg"	=> $powers[7],
					  "Warsaw"			=> $powers[7],
					  "Sevastopol"		=> $powers[7],
					  "Moscow"			=> $powers[7],
					  "Belgium"			=> "",
					  "Holland"			=> "",
					  "Denmark"			=> "",
					  "Sweden"			=> "",
					  "Norway"			=> "",
					  "Spain"			=> "",
					  "Portugal"		=> "",
					  "Tunis"			=> "",
					  "Serbia"			=> "",
					  "Rumania"			=> "",
					  "Bulgaria"		=> "",
					  "Greece"			=> "");

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

	preg_match("/Autumn, 1901 .*div class=\"hr\"/s", $page, $f01);
	preg_match_all("/span class=\"country.\">(\w*).*?Diplomacy.*?(<ul>.*?)<li><a name=\"index/s", $f01[0], $powermoves, PREG_SET_ORDER);
	foreach($powermoves as $orderset)
	{
		$powername = $orderset[1];
		preg_match_all("/<li>(.*?)<\/li>/", $orderset[2], $movelist);
		foreach($movelist[1] as $move)
		{
			if(strpos($move, "(dislodged)") !== false)
				continue;
			if(strpos($move, "convoy to") !== false)
				continue;
			if(preg_match("/ at (.*) (support)/", $move, $movearray))
				$homespace = $movearray[1];
			elseif(preg_match("/ at (.*) hold\./", $move, $movearray))
				$homespace = $movearray[1];
			else
			{
				preg_match("/ at (.*) move to (.*?)(via convoy)?\./", $move, $movearray);
				if(strpos($move, "(fail)") !== false)
					$homespace = $movearray[1];
				else
					$homespace = $movearray[2];
			}
			echo("<br>$homespace");
		}
	}
?>
