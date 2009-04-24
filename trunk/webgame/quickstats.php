<?php
$alive      = false;
$private    = true;
$quickstat  = false;
$page_title = "Quickstats";

include_once("interface/header.php");
require_once(LIB_ROOT."specific/lib_status.php");
require_once(LIB_ROOT."specific/lib_mail.php");

// *** Turning the header variables into variables for this page.
$command  = in('command');
$health   = $players_health;
$strength = $players_strength;
$gold     = $players_gold;
$kills    = $players_kills;
$turns    = $players_turns;
$level    = $players_level;
$class    = $players_class;
$bounty   = $players_bounty;
$status   = $players_status;  //The status variable is an array, of course.




$mail_count = mail_count();
$standard_stats = array(
    "Turns" => $turns,
    "Gold" => $gold,
    "Bounty" => $bounty,
    "Mail" => $mail_count,
);

echo "<div class='quickstats'>";
echo render_health($health); // Display current health.
//if ($command != "viewinv") {
    echo "<div class='player-stats'>";
    echo render_stats($standard_stats); // Display the stats.
    echo "</div>";
//} else if ($command == "viewinv") { // Display all the items in the inv.
    echo "<div class='inventory'>";
    echo render_inventory();
    echo "</div>";
//}
echo "</div>";



?>
</body>
</html>




