
<?php global $active_nav; ?>
<div id="subNav">
<div id="subNavShell">
  <ul class="tabset_tabs">
  
	<li><a <?php if ($active_nav == 'admin') print 'class="active" '; ?> href="admin.php">Admin</a></li>
	<li><a <?php if ($active_nav == 'email') print 'class="active" '; ?> href="email.php">Email Management</a></li>
	<li><a <?php if ($active_nav == 'email_queue') print 'class="active" '; ?> href="email_queue.php">Email Queue Editor</a></li>
	<li><a <?php if ($active_nav == 'players') print 'class="active" '; ?> href="players.php">Players Editor</a></li>
    
  </ul>
</div>
</div>
<br />
