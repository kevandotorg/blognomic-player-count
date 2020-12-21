<?

$year = 2005;
$month = 6;

while ($year<2021)
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://blognomic.com/$year/".sprintf('%02d', $month)."/");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $archive = curl_exec($ch);
        curl_close($ch);

        preg_match_all("/posted by <a href=\"\/member\/\d+\">([^<]+)<\/a>/",$archive,$matches);

        $players = array();
        foreach($matches[1] as $name)
        {
                if(!in_array($name, $players))
                { array_push($players,$name); }
        }

        natcasesort($players);

        $row = "$year-".sprintf('%02d', $month).",".sizeof($players).",\"";
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
