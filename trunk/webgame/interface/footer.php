<?php
require_once(substr(__FILE__,0,(strpos(__FILE__, 'webgame/')))."webgame/lib/base.inc.php"); // *** Absolute path include of everything.

// Displays the ending html stuff, and potentially the quickstats js refresh.
render_footer($quickstat);

?>
