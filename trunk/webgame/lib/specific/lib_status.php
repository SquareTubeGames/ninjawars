<?php




/*
 * Returns a comma-seperated string of states based on the statuses of the target.
 * @param array $statuses status array
 * @param string $target the target, username if self targetting.
 * @return string
 *
 * @package player
 * @subpackage status
 */
function statuses($statuses=null, $target=null) {
	$states = array();
	$result = '';
	$target = isset($target)? $target : get_username();
	if (!$statuses) {
		$statuses = getStatus($target);
	}
	$health = getHealth($target);
	
	if ($health<1) {
	    $states['Dead'] = "Dead"; 
	} else { // *** Other statuses only display if not dead.
		if ($health < 100) { 
		    $states['Injured'] = "Injured"; 
		}
		if ($statuses['Stealth']) { $states['Stealthed'] = "Stealthed"; }
		if ($statuses['Poison']) { $states['Poisoned'] = "Poisoned"; }
		if ($statuses['Frozen']) { $states['Frozen'] = "Frozen"; }
	}
	assert($target != '' && $states != '');
	return $states;
}

/**
 * Pull the items and their amounts for a certain user.
**/
function get_inventory($username=null){
    if(!$username){
        $username = get_username();
    }
    $sel_amounts = "select item, amount from inventory where owner = '$username' order by item";
    $sql = new DBAccess();
    $sql->Query($sel_amounts);
    return $items_and_amounts = $sql->FetchAll();
}

/**
 * Render the health.
**/
function render_health($health){
    $username = get_username();
    $statuses = statuses(getStatus($username), $username);
    $status_list = implode(',', $statuses);
    $low_health_class = '';
    if($health<100){ // Make health display red if it goes below 80.
        $low_health_class = "ninja-error";
    }
    $out = "<div class='status'><span class='health $low_health_class'>Health: $health</span>
        <span class='status-list'>$status_list</span></div>";
    return $out;
}


/**
 * Render the misc. stats.
**/
function render_stats($standard_stats){
    $out = "<dl>";
    foreach($standard_stats as $lstat => $lval){
        $out .= "<dt>$lstat:</dt> <dd>$lval</dd>";
    }
    $out .= "</dl>";
    return $out;
}


/**
 * Display the items in the inventory.
**/
function render_inventory(){
    $gold = getGold(get_username());
    $out = "<dl class='inventory'>";
    $inventory = get_inventory();
    foreach($inventory AS $litem) {
        $out .= "<dt>".$litem['item'].":</dt> <dd>".$litem['amount']."</dd>";
    }
    $out .= "</dl>";
    return $out;
}




	
?>
