<?php


/**
 * Delete an array of ids or all mail for a certain user.
**/
function delete_mail($ids, $all=false){
    $sql = new DBAccess();
    $deleted = 0;
    $username = get_username();
    if($all){ // Delete all a user's mail.
        $del = "DELETE from mail where send_to='".$username."'";
    } else { // Delete an id list.
        $del = "DELETE from mail where send_to='".$username."' 
            AND id in ('".implode("', '", $ids)."')";
    }
    $sql->Delete($del);
    $deleted = $sql->a_rows;
    return $deleted;
}

/**
 * Pull the count of mail.
**/
function mail_count(){
    $username = get_username();
    $sql = new DBAccess();
    return $sql->QueryItem("SELECT count(*) FROM mail WHERE send_to = '$username'");
}




?>
