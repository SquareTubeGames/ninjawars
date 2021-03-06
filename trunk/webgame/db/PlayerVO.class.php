<?php
/*
 * Value object for holding a player's data from the database.
 * Fields have to be added here and in the PlayerDAO.
 * Essentially this acts as the container for the model's data.
 * @var database_fields
 */
class PlayerVO {
	var $player_id, $uname, $pname, $health, $strength, $gold,
	  	$messages, $kills, $turns, $confirm, $confirmed, $email,
	  	$class, $level, $status, $member, $days, $ip, $bounty, $clan,
	  	$clan_long_name, $created_date, $last_started_attack, $energy;
}
?>
