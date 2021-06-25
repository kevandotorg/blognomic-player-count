<?

$year = 2005;
$month = 6;

$allplayers = array();

print "title,proposer,postedDate,status,admin,closedDate,lifespan,comments\n";
	
while ($year<2021 || $month < 7)
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
	
foreach ($dom->getElementsByTagName('div') as $div){

	$title = ""; $status = "?"; $posted = ""; $closed = ""; $poster = "";
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
					$poster = $a->nodeValue;
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
		if ($closed == "") { $lifespan = -1; }
		
		$csv = "\"$title\",\"$poster\",\"$posted\",\"$status\",\"$admin\",\"$closed\",$lifespan,$comments\n".$csv;
	}
	
	if ($proposal == 1 && $toskip>0) { $toskip--; }
}

	print $csv;
        $month++;
        if ($month==13) { $month = 1; $year++; }
}
?>

