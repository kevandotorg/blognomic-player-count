<?

$year = 2005;
$month = 6;

$allplayers = array();

print "Month,Active Players,New Players,Proposals,Names\n";

while ($year<date("Y") || $month < date("n")+1)
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://blognomic.com/$year/".sprintf('%02d', $month)."/");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $archive = curl_exec($ch);
        curl_close($ch);

        preg_match_all("/posted by <a href=\"\/member\/\d+\">([^<]+)<\/a>/",$archive,$matches);

        $players = array();
        $new = 0;
        foreach($matches[1] as $name)
        {
                if(!in_array($name, $players))
                { array_push($players,$name); }
                if(!in_array($name, $allplayers))
                { array_push($allplayers,$name); $new++; }
        }

        natcasesort($players);

        $proposals = substr_count($archive,'">Proposal:');

        $row = "$year-".sprintf('%02d', $month).",".sizeof($players).",$new,$proposals,\"";
        foreach ($players as $player)
        {
                $row .= $player.", ";
        }
        $row = substr($row, 0, -2);

        print $row."\"\n";
        $month++;
        if ($month==13) { $month = 1; $year++; }
}


?>
