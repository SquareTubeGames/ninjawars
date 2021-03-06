<?php
/*
 * Deals with the skill based attacks, and status effects.
 * 
 * @package combat
 * @subpackage skill
 */
$private    = true;
$alive      = true;
$quickstat  = "player";
$page_title = "Using Skills";

include "interface/header.php";
include(OBJ_ROOT."Skill.php");
?>

<span class="brownHeading">Skills You Possess</span><br />

<?php
//Get filtered info from input.
$target = in('target');
$command = in('command');
$stealth = in('stealth');


$skillListObj = new Skill();
$poisonMaximum = 100; // *** Before level-based addition.
$poisonMinimum = 1;
$poisonTurnCost = $skillListObj->getTurnCost('poison touch');
$turn_cost = $skillListObj->getTurnCost(strtolower($command));
$ignores_stealth = $skillListObj->getIgnoreStealth($command);
$self_use = $skillListObj->getSelfUse($command);
$use_on_target = $skillListObj->getUsableOnTarget($command);

$starting_turn_cost = $turn_cost;
assert($turn_cost>=0);
$turns_to_take = null;  // *** Even on failure take at least one turn.

if ($target != "") {
  $link_back = "<a href=\"player.php?player=$target\">Player Detail</a>";
} else {
  $target    = $username;
  $link_back = "<a href=\"skills.php\">Skills</a>";
}
$user_ip   = $_SESSION['ip'];
$username_status = getStatus($username);
$class     = getClass($username);
$target_hp = getHealth($target);
$target_ip = $sql->QueryItem("SELECT ip FROM players WHERE uname = '$target'");
$target_turns = $sql->QueryItem("SELECT turns FROM players WHERE uname = '$target'");
$level = getLevel($username);
$covert       = false;
$victim_alive = true;
$attacker_id  = $username;
$starting_turns = getTurns($username);
$ending_turns = null;

$level_check  = $level - getLevel($target);

if ($username_status && $status_array['Stealth']) {
  $attacker_id = "A Stealthed Ninja";
}

// TODO: Make attackLegal use self_use param.
// TODO: Make attackLegal also check that the skill can be used on an outside target.
// *** Checks the skill use legality, as long as the target isn't self.
$params = array('required_turns'=>$turn_cost, 'ignores_stealth'=>$ignores_stealth, 'self_use'=>$self_use);
$AttackLegal = new AttackLegal($username, $target, $params);
$attack_allowed = $AttackLegal->check();
$attack_error = $AttackLegal->getError();

if($attack_error){ // Use AttackLegal if not attacking self.
	echo "<div class='ninja-notice'>$attack_error</div>"; // Display the reason for the attack failure.
} elseif ( $class == "" || $command == "") {
	echo "Your class did not match the requested skill.\n";
} else {
	// Initial attack conditions are alright.
	  echo "Preparing to use skill...<br />\n";
	  $result = "";
	  
	  if ($class == "White") {
	      if ($command == "Sight"){
		  $covert    = true;
		  
		  if ($starting_turns >= $turn_cost){
		      $msg = "You have had sight cast on you by $attacker_id at $today";
		      sendMessage($attacker_id,$target,$msg);
		      $username_status = subtractStatus($target, STEALTH);
		      
		      $sql->Query("SELECT uname,class,health,strength,gold,kills,turns,level FROM players WHERE uname='$target'");
		      
		      $data = $sql->FetchAssociative();
	      
		      echo "<table>\n";
		      echo "<tr>\n";
		      echo "  <th>Name</th>\n";
		      echo "  <th>Class</th>\n";
		      echo "  <th>Health</th>\n";
		      echo "  <th>Str</th>\n";
		      echo "  <th>Gold</th>\n";
		      echo "  <th>Kills</th>\n";
		      echo "  <th>Turns</th>\n";
		      echo "  <th>Level</th>\n";
		      echo "</tr>\n";
		      echo "<tr>\n";
		      //var_dump($data);
		      //var_dump($target);
		      //var_dump('DEBUG: ');
		      //print_r($sql->data);
		      
		     foreach($data AS $loopPart){
			     echo "<td>".$loopPart."</td>\n";
		     }
		     /*
		      for ($i = 0; $i <= 12; $i++)
			{
			  echo "<td>".$sql->data[$i]."</td>\n";
			}
		      */
		      echo "</tr>";
		      echo "</table>";
		    } else {
		      $turn_cost = 0;
		      echo "You do not have enough turns to cast $command.\n";
		    }
		}
	    } else if ($class == "Black") {
			if  ($command == "Steal") {
			  $covert = true;
			  
			  if ($starting_turns >= $turn_cost) {
                    $gold_decrease = rand(1, 50);
                    $target_gold = getGold($target);
                    $gold_decrease = ($target_gold<$gold_decrease? $target_gold : $gold_decrease);
                    changeGold($username,$gold_decrease); // *** This one actually adds the value.
			        subtractGold($target,$gold_decrease); // *** Subtracts whatever positive value is put in.
			      
			      $msg = "You have had pick pocket cast on you for $gold_decrease by $attacker_id at $today";
			      sendMessage($attacker_id,$target,$msg);
			      
			      $result = "You have stolen $gold_decrease gold from $target!<br />\n";
			    } else {
			      $turn_cost = 0;
			      echo "You do not have enough turns to cast $command.\n";
			    }
			} else if ($command == "Unstealth") {
				$mode = (-1*STEALTH);
				$state = "unstealthed";
				if ($starting_turns >= $turn_cost) {
					if ($status_array['Stealth']==true) {
						addStatus($target,$mode);
						echo "You are now $state.<br />\n";
					} else {
						$turn_cost = 0;
						echo "$target is already $state.\n";
					}
				} else {
					$turn_cost = 0;
					echo "You do not have enough turns to cast $command.\n";
				}
			}
			else if ($command == "Stealth") {
			  $covert     = true;
			  $mode       = STEALTH;
			  $state      = "stealthed";

			  if ($starting_turns >= $turn_cost){
			      if (false==$status_array['Stealth']){
				  addStatus($target,$mode);
				  echo "You are now $state.<br />\n";
				} else {
				  $turn_cost = 0;
				  echo "$target is already $state.\n";
				}
			    } else {
			      $turn_cost = 0;
			      echo "You do not have enough turns to cast $command.\n";
			    }
			} else if ($command == "Poison Touch") {
			  $covert = true;
			  
			  if($starting_turns >= $turn_cost) {
			      addStatus($target,POISON);
			      
			      $target_damage = rand($poisonMinimum,$poisonMaximum);
			      
			      $victim_alive = subtractHealth($target,$target_damage);
			      echo "$target has beeen poisoned!<br />\n";
			      echo "$target's HP reduced by $target_damage!<br />\n";
			      
			      $msg = "You have been poisoned by $attacker_id at $today";
			      sendMessage($attacker_id,$target,$msg);
			    } else {
			      $turn_cost = 0;
			      echo "You do not have enough turns to cast $command.\n";
			    }
			} 
	    } else if ($class == "Red") {
	      if ($command == "Fire Bolt") {
		  
		  if ($starting_turns >= $turn_cost) {
				$target_damage = (5*(ceil($level/3))+rand(1,getStrength($username)));

				echo "$target's HP reduced by $target_damage!<br />\n";

				if($victim_alive = subtractHealth($target,$target_damage)) {
				  	$attacker_id  = $username;
				}

				$msg = "You have had fire bolt cast on you by $attacker_id at $today";
				sendMessage($attacker_id,$target,$msg);
		    } else {
		      $turn_cost = 0;
		      echo "You do not have enough turns to cast $command.\n";
		    }
		}
	    } else if ($class == "Blue") {
	      if ($command == "Ice Bolt"){
		  
		  if ($starting_turns >= $turn_cost) {
		      $turns_decrease = rand(1,5);
		      subtractTurns($target,$turns_decrease);
		      // Changed ice bolt to kill stealth.
		      $username_status = subtractStatus($target, STEALTH);
		      
		      $msg = "Ice bolt cast on you by $attacker_id at $today, your turns have been reduced by $turns_decrease.";
		      sendMessage($attacker_id,$target,$msg);
		      
		      $result = "$target's turns reduced by $turns_decrease!<br />\n";
		    } else {
		      $turn_cost = 0;
		      echo "You do not have enough turns to cast $command.\n";
		    }
		} else if ($command == "Cold Steal") {
		  if ($starting_turns >= $turn_cost) {
		      $critical_failure = rand(1,100);

		      if ($critical_failure > 7) {// *** If the critical failure rate wasn't hit.
			  	if($target_turns >= 5){
			      $turns_decrease = rand(2,6);
			      
			      subtractTurns($target,$turns_decrease);
			      addTurns($username,$turns_decrease);
			      
			      $msg = "You have had Cold Steal cast on you for $turns_decrease by $attacker_id at $today";
			      sendMessage($attacker_id,$target,$msg);
			      
			      $result = "You cast Cold Steal on $target and take $turns_decrease of his turns.<br />\n";
			    } else {
			      $turn_cost = 0;
			      $result = "The victim did not have enough turns to give you.<br />\n";
			    }
			} else { // *** CRITICAL FAILURE !!
			  addStatus($username,FROZEN);

			  $unfreeze_time = date("F j, Y, g:i a",mktime(date("G")+1,0,0,date("m"),date("d"),date("Y")));
			  
			  $failure_msg = "You have experienced a critical failure while using Cold Steal on $today. You will be unfrozen on $unfreeze_time";
			  sendMessage("SysMsg",$username,$failure_msg);
			  $result = "Cold Steal has backfired! You have lost 3 turns and are now frozen until $unfreeze_time!<br />\n";
			}
		    } else {
		      $turn_cost = 0;
		      echo "You do not have enough turns to cast $command.\n";
		    }
		}
	    }
	  
	  echo $result;

	  if (!$victim_alive) {
	    if ($target != $username) {
		  $gold_mod = 0.15;
		  $loot     = round($gold_mod*getGold($target));
	  
		  subtractGold($target,$loot);
		  addGold($username,$loot);
		  
		  addKills($username,1);
		  
		  echo "You have killed $target with $command!<br />\n";
		  echo "You receive $loot gold from $target.<br />\n";

		  $added_bounty = floor($level_check/5);
		  
		  if ($added_bounty>0) {
		      addBounty($username,($added_bounty*25));
		      echo "Your victim was much weaker than you. The townsfolk are angered. A bounty of ".($added_bounty*25)." gold has been placed on your head!<br />\n";
		    } else {
		      if($bounty = rewardBounty($username, $target)) {
				  echo "You have received the $bounty gold bounty on $target's head for your deeds!<br />\n";
				  
				  $bounty_msg = "You have valiantly slain the wanted criminal, $target! For your efforts, you have been awarded $bounty gold!";
				  sendMessage("Village Doshin",$username,$bounty_msg);
				}
		    }
		  
		  $target_message = "$attacker_id has killed you with $command on $today and taken $loot gold.";
		  sendMessage($attacker_id,$target,$target_message);

		  $attacker_message = "You have killed $target with $command on $today and taken $loot gold.";
		  sendMessage($target,$username,$target_message);
		} else {
		  $loot = 0;
		  echo "You have comitted suicide!<br />\n";
		}
	  }
	    
	$turns_to_take = $turns_to_take - $turn_cost;

	if (!$covert && getStatus($username) && $status_array['Stealth']) {
	    subtractStatus($username,STEALTH);
	    echo "Your actions have revealed you. You are no longer stealthed.<br />\n";
	}
} // End of the skill use SUCCESS block.

$ending_turns = changeTurns($username, $turns_to_take);

?>
<div class="skillReload">
    <a href="skills_mod.php?command=<?php echo urlencode($command); ?>&amp;target=<?php echo $target; ?>">Use <?php echo $command; ?> again.</a>
</div>
<br>
<div class="LinkBack">
    Return to <?echo $link_back;?>
</div>

</p>

<?php
include "interface/footer.php";
?>

