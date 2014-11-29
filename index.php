<?php
	//So that way I'll know if something goes wrong
	error_reporting(E_ALL);
	if(!isset($_GET['game']))
		exit;
	$game = $_GET['game'];
	$page = file_get_contents("http://webdiplomacy.net/board.php?gameID=$game&viewArchive=Orders");
	$powers = array(1 => "England", "France", "Italy", "Germany", "Austria", "Turkey", "Russia");
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
	$shortnames = array("Liverpool"				=> "Lvp",
						"London"				=> "Lon",
						"Edinburgh"				=> "Edi",
						"Marseilles"			=> "Mar",
						"Paris"					=> "Par",
						"Brest"					=> "Bre",
						"Venice"				=> "Ven",
						"Rome"					=> "Rom",
						"Naples"				=> "Nap",
						"Munich"				=> "Mun",
						"Berlin"				=> "Ber",
						"Kiel"					=> "Kie",
						"Trieste"				=> "Tri",
						"Budapest"				=> "Bud",
						"Vienna"				=> "Vie",
						"Ankara"				=> "Ank",
						"Smyrna"				=> "Smy",
						"Constantinople"		=> "Con",
						"St. Petersburg"		=> "StP",
						//Don't do this at home, kids. It's bad practice!
						"St"					=> "StP",
						"Warsaw"				=> "War",
						"Sevastopol"			=> "Sev",
						"Moscow"				=> "Mos",
						"Belgium"				=> "Bel",
						"Holland"				=> "Hol",
						"Denmark"				=> "Den",
						"Sweden"				=> "Swe",
						"Norway"				=> "Nor",
						"Spain"					=> "Spa",
						"Portugal"				=> "Por",
						"Tunis"					=> "Tun",
						"Serbia"				=> "Ser",
						"Rumania"				=> "Rum",
						"Bulgaria"				=> "Bul",
						"Greece"				=> "Gre",
						"Clyde"					=> "Cly",
						"Yorkshire"				=> "Yor",
						"Wales"					=> "Wal",
						"Picardy"				=> "Pic",
						"Gascony"				=> "Gas",
						"Burgundy"				=> "Bur",
						"North Africa"			=> "NAf",
						"Ruhr"					=> "Ruh",
						"Silesia"				=> "Sil",
						"Prussia"				=> "Pru",
						"Tyrolia"				=> "Tyr",
						"Bohemia"				=> "Boh",
						"Galicia"				=> "Gal",
						"Finland"				=> "Fin",
						"Livonia"				=> "Lvn",
						"Ukraine"				=> "Ukr",
						"Apulia"				=> "Apu",
						"Tuscany"				=> "Tus",
						"Piedmont"				=> "Pie",
						"Albania"				=> "Alb",
						"Syria"					=> "Syr",
						"Armenia"				=> "Arm",
						"Barents Sea"			=> "Bar",
						"Norwegian Sea"			=> "Nwg",
						"North Sea"				=> "Nth",
						"English Channel"		=> "Eng",
						"Gulf of Bothnia"		=> "GoB",
						"Baltic Sea"			=> "Bal",
						"Skaggerack"			=> "Ska",
						"Heligoland Bight"		=> "Hel",
						"North Atlantic Ocean"	=> "NAO",
						"Mid-Atlantic Ocean"	=> "MAO",
						"Western Mediterranean"	=> "WMs",
						"Gulf of Lyons"			=> "GoL",
						"Tyrrhenian Sea"		=> "TyS",
						"Ionian Sea"			=> "Ion",
						"Adriatic Sea"			=> "Adr",
						"Aegean Sea"			=> "Aeg",
						"Eastern Mediterranean"	=> "EmS",
						"Black Sea"				=> "Bla");

	//Getting the final SC counts
	//The width of the occupation bar corrosponds to the percentage of all the SCs a power owns.
	preg_match_all("/occupationBar(\d).*?width:(\d*)/", $page, $finalscs, PREG_SET_ORDER);
	$sccount = array_fill_keys($powers, 0);
	foreach($finalscs as $powersc)
	{
		$powername = $powersc[1];
		$totalscs = round($powersc[2]*34/100);
		$sccount[$powers[$powername]] = $totalscs;
	}
	$finalstmt = $mysqli->prepare("INSERT INTO dipfinal (game, power, scs) VALUES(?, ?, ?)");
	$finalstmt->bind_param("isi", $game, $powername, $totalscs);
	foreach($sccount as $powername => $totalscs)
		$finalstmt->execute();
	$finalstmt->close();

	//Getting the Spring 1901 moves
	preg_match("/Spring, 1901 .*\/p>/s", $page, $s01);
	preg_match_all("/The \w* at ([\w -\.]*\.)</", $s01[0], $moves);
	$movestmt = $mysqli->prepare("INSERT INTO dipmoves (game, sc, move) VALUES(?, ?, ?)");
	$movestmt->bind_param("iss", $game, $startspace, $order);
	foreach($moves[1] as $unit)
	{
		//Welcome to the wonderful world of regular expressions!
		if(preg_match("/(.*?)(?: \(\w* Coast\))? (support) (move) to (.*) from (.*)\./", $unit, $unitarray) == 0)
			if(preg_match("/(.*?)(?: \(\w* Coast\))? (support) (hold) to (.*)\./", $unit, $unitarray) == 0)
				if(preg_match("/(.*)(?: \(\w* Coast\))? (convoy) to (.*) from (.*)\./", $unit, $unitarray) == 0)
					if(preg_match("/(.*?)(?: \(\w* Coast\))? (move) to (.*?)( via convoy)?\./", $unit, $unitarray) == 0)
						preg_match("/(.*?)(?: \(\w* Coast\))? (hold)\./", $unit, $unitarray);
		$startspace = $shortnames[$unitarray[1]];
		$moveaction = "-";
		//Depending on the type of order issued...
		switch($unitarray[2])
		{
			case "hold":
				$action = "h";
				$order = $action;
				break;
			case "move":
				$destination = $shortnames[$unitarray[3]];
				$order = "$moveaction $destination";
				break;
			case "support":
				$action = "s";
				if($unitarray[3] == "move")
				{
					$supported = $shortnames[$unitarray[5]];
					$sdestination = $shortnames[$unitarray[4]];
					$order = "$action $supported $moveaction $sdestination";
				}
				else
				{
					$supported = $shortnames[$unitarray[4]];
					$order = "$action $supported";
				}
				break;
			case "convoy":
				$action = "c";
				$convoyed = $shortnames[$unitarray[4]];
				$cdestination = $shortnames[$unitarray[3]];
				$order = "$action $convoyed $moveaction $cdestination";
				break;
		}
		$movestmt->execute();
	}
	$movestmt->close();

	//Getting the SC ownership after 1901
	//Default SC owners were defined higher up, and those are overridden by whoever happens to be in a territory after Fall 1901
	preg_match("/Autumn, 1901 .*?div class=\"hr\"/s", $page, $f01);
	preg_match_all("/span class=\"country.\">(\w*).*?Diplomacy.*?(<ul>.*?)<a name=\"index/s", $f01[0], $powermoves, PREG_SET_ORDER);
	foreach($powermoves as $orderset)
	{
		$powername = $orderset[1];
		preg_match_all("/<li>(.*?)<\/li>/", $orderset[2], $movelist);
		foreach($movelist[1] as $move)
		{
			//Dislodged units will be dealt with later
			if(strpos($move, "(dislodged)") !== false)
				continue;
			//You can only convoy in water
			if(strpos($move, "convoy to") !== false)
				continue;
			//Supporting and holding units remain at their initial location
			if(preg_match("/ at (.*) (support)/", $move, $movearray))
				$homespace = $movearray[1];
			elseif(preg_match("/ at (.*) hold\./", $move, $movearray))
				$homespace = $movearray[1];
			//Get either the start or destination based on whether the move was successful
			else
			{
				preg_match("/ at (.*) move to (.*?)( via convoy)?\./", $move, $movearray);
				if(strpos($move, "(fail)") !== false)
					$homespace = $movearray[1];
				else
					$homespace = $movearray[2];
			}
			if($offset = strpos($homespace, " ("))
				$homespace = substr($homespace, 0, $offset);
			if(array_key_exists($homespace, $scowners))
				$scowners[$homespace] = $powername;
		}
	}
	//And now do the same thing for retreats
	preg_match_all("/class=\"country.\">(\w*).*\n.*Retreats.*\n(.*?)<\/ul>/", $f01[0], $retreatmoves, PREG_SET_ORDER);
	foreach($retreatmoves as $orderset)
	{
		$powername = $orderset[1];
		preg_match_all("/<li>(.*?)<\/li>/", $orderset[2], $movelist);
		foreach($movelist[1] as $move)
		{
			if(strpos($move, "</u>") !== false)
				continue;
			if(preg_match("/ retreat to (.*?)(\(.*\))?\./", $move, $movearray))
			{
				$homespace = $movearray[1];
				if(array_key_exists($homespace, $scowners))
					$scowners[$homespace] = $powername;
			}
		}
	}
	$scstmt = $mysqli->prepare("INSERT INTO dipscs (game, sc, power) VALUES(?, ?, ?)");
	$scstmt->bind_param("iss", $game, $sc, $powername);
	//Shorten the names for ease of use later on
	foreach($scowners as $longsc => $powername)
	{
		$sc = $shortnames[$longsc];
		$scstmt->execute();
	}
	$scstmt->close();
	echo("Yay!");
?>
