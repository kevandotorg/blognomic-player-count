<?

$year = 2005;
$month = 6;

$allplayers = array();

print "title,proposer,postedDate,status,admin,closedDate,lifespan,comments,dynasty\n";

while ($year<date("Y") || $month < date("n")+1)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://blognomic.com/$year/".sprintf('%02d', $month)."/");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$archive = curl_exec($ch);
	curl_close($ch);

	$dom = new DOMDocument();

	@$dom->loadHTML($archive);
	
	$toskip = 3;
	$csv = "";
	
	foreach ($dom->getElementsByTagName('div') as $div)
	{
		$title = ""; $status = "?"; $posted = ""; $closed = ""; $poster = ""; $admin = ""; $closenote = "";
		$proposal = 0;

		foreach ($div->getElementsByTagName('h3') as $h3){
			if (preg_match("/(Enacted|Failed|Vetoed|Illegal|open)/",$h3->getAttribute('class'),$matches)) { $status = ucfirst($matches[1]); }
			if (preg_match("/(proposal)/",$h3->getAttribute('class'),$matches)) { $proposal = 1; }
			foreach ($h3->getElementsByTagName('a') as $a){
				$title = $a->nodeValue;
			}		
		}

		$previous = "";
		foreach ($div->getElementsByTagName('div') as $div2){
			foreach ($div2->getElementsByTagName('div') as $div3){
				foreach ($div3->getElementsByTagName('div') as $div4){
					foreach ($div4->getElementsByTagName('p') as $p){
						if (preg_match("/Adminned at (.+) UTC/",$p->nodeValue,$matches)) { $closed = $matches[1]; $closenote = $previous; }
						$previous = $p->nodeValue;
					}
				}		
			}
			foreach ($div2->getElementsByTagName('p') as $p){
				foreach ($p->getElementsByTagName('em') as $em){
					if (preg_match("/at (.+)  UTC/",$p->nodeValue,$matches)) { $posted = $matches[1]; }
					foreach ($em->getElementsByTagName('a') as $a){
						if (preg_match("/^\/member\/\d+/",$a->getAttribute('href')))
						{ $poster = $a->nodeValue; }
					}
				}
				foreach ($p->getElementsByTagName('a') as $a){
					if (preg_match("/Comments \((.+)\)/",$a->nodeValue,$matches)) { $comments = $matches[1]; }
				}
			}
		}

		if ($proposal == 1 && $toskip == 0)
		{
			$title = preg_replace("/^Proposal: /","",$title);
			$admin = "?";
			
			if (preg_match("/(90000|aaronwinborn|alethiophile|Amnistar|Angry Grasshopper|arthexis|Axeling|Cayvie|Chivalrybean|ChronosPhaenon|Clucky|Darknight|Devenger|Elias IX|epylar|Excalabur|gobleteer|Greth|Hix|Jack|jay|Klisz|Larrytheturtle|lilomar|Ornithopter|Oze|Personman|Plorkyeran|Prince Anduril|Qwazukee|RaichuKFM|Rodney|Saki|Saurik|scshunt|Seebo|Seventy-Fifth Trombone|Shadowclaw|SingularByte|Skju|smith|southpointingchariot|Spitemaster|Tantusar|Thelonious|Thrawn|TrumanCapote|Wakukee|Yoda|Zeofar|Kevan|Josh|Seventy-Fifth Trombone|Purplebeard|Bucky|Clucky|Brendan|Darknight|Ienpw III|quirck|Tantusar|pokes|Cuddlebeam|derrick|card|Publius Scribonius Scholasticus|Jumble)/",$closenote,$matches))
			{
				$admin = $matches[sizeof($matches)-1];
			}
			
			$lifespan = floor(abs(strtotime($closed) - strtotime($posted))/60/60);
			
			if ($lifespan == 105234) { $lifespan = 48; } // fix mystery case for https://blognomic.com/archive/soul_grind which has an incorrect 12-year timestamp
			
			if ($closed == "") { $lifespan = -1; }
			if ($status == "Open") { $admin = "-"; }
			
			$csv = "\"$title\",\"$poster\",\"$posted\",\"$status\",\"$admin\",\"$closed\",$lifespan,$comments,".whichDynasty($posted)."\n".$csv;
		}
		
		if ($proposal == 1 && $toskip>0) { $toskip--; }
	}

	print $csv;
	
	$month++;
	if ($month==13) { $month = 1; $year++; }
}

function whichDynasty($propdate)
{
	$enddates = array("2003-4-9","2003-5-18","2003-6-23","2003-8-12","2003-9-14","2003-9-18","2003-10-12","2003-11-23","2004-1-6","2004-1-12","2004-2-1","2004-2-21","2004-3-8","2004-4-26","2004-5-10","2004-6-18","2004-9-6","2004-10-16","2004-11-6","2004-11-29","2004-12-12","2005-1-2","2005-1-25","2005-4-3","2005-5-20","2005-6-27","2005-8-3","2005-8-22","2005-9-11","2005-10-22","2005-12-3","2005-12-13","2006-1-16","2006-3-3","2006-4-2","2006-7-4","2006-8-26","2006-9-25","2006-11-1","2006-12-11","2007-2-2","2007-2-26","2007-3-9","2007-3-30","2007-4-29","2007-6-5","2007-7-30","2007-8-29","2007-10-15","2007-11-18","2007-12-28","2008-3-4","2008-4-8","2008-5-21","2008-7-9","2008-8-4","2008-9-20","2008-10-17","2008-11-15","2008-12-19","2009-1-14","2009-2-11","2009-3-13","2009-5-14","2009-6-25","2009-7-29","2009-8-25","2009-10-7","2009-11-4","2009-12-9","2010-1-14","2010-2-26","2010-4-1","2010-4-3","2010-4-24","2010-5-8","2010-6-2","2010-7-27","2010-8-16","2010-9-6","2010-9-30","2010-11-3","2010-11-29","2010-12-18","2011-1-9","2011-2-18","2011-3-28","2011-4-10","2011-4-22","2011-5-17","2011-7-2","2011-8-25","2011-9-30","2011-10-25","2011-11-10","2012-1-3","2012-1-31","2012-3-16","2012-5-28","2012-6-17","2012-7-24","2012-8-9","2012-9-11","2012-10-18","2012-11-11","2012-12-14","2013-1-21","2013-2-27","2013-4-1","2013-6-9","2013-7-15","2013-9-4","2013-10-18","2013-11-7","2013-12-27","2014-3-4","2014-4-4","2014-5-5","2014-6-14","2014-7-15","2014-9-1","2014-11-23","2015-1-18","2015-2-21","2015-4-6","2015-5-11","2015-6-8","2015-7-1","2015-7-23","2015-8-4","2015-8-20","2015-10-6","2015-11-1","2015-12-2","2016-1-17","2016-2-13","2016-3-4","2016-4-26","2016-5-23","2016-6-16","2016-8-8","2016-9-19","2016-11-2","2016-12-10","2017-3-3","2017-4-13","2017-5-21","2017-7-11","2017-7-24","2017-9-8","2017-10-5","2017-11-4","2017-12-11","2018-1-18","2018-3-8","2018-5-6","2018-6-19","2018-8-1","2018-8-29","2018-9-26","2018-10-19","2018-11-23","2019-1-20","2019-2-13","2019-4-12","2019-5-19","2019-6-23","2019-7-28","2019-9-10","2019-10-14","2019-11-5","2019-12-9","2020-1-3","2020-2-6","2020-3-12","2020-4-8","2020-5-2","2020-5-26","2020-8-12","2020-9-9","2020-10-20","2020-11-29","2021-1-24","2021-2-20","2021-3-15","2021-4-13","2021-5-12","2021-6-8");

	$dynasty=1;
	foreach ($enddates as $enddate)
	{
		if (strtotime($propdate) > strtotime($enddate))
		{ $dynasty++; }
	}
	
	return $dynasty;
}
?>

