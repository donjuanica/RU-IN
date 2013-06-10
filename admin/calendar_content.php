<div width="500px" style=" margin: 10px 2px;
	padding: 2px; 
	border-color:#666666;
	border-style:solid;
	border-width: 3px;
    width: 450px;  " >
<form action='' method='POST'>
<table cellpadding=2 cellspacing=2 border=0>
        <tr valign="middle">
          <td valign="top">
            <strong>Start Date</strong><br>
            <div style=" margin: 1em 1em 0 0" id="calendar1"></div>
            <script type="text/javascript">//<![CDATA[
            function flatCalendarCallback(cal) {
              if (cal.dateClicked) {
            document.getElementById('start').value=cal.date.print("%Y-%m-%d");
              }
            };
            //]]></script>
            <script type="text/javascript">//<![CDATA[
            Zapatec.Calendar.setup({
              step              : 1,
              flat              : "calendar1",
              flatCallback      : flatCalendarCallback
            , electric          : false
            , noHelp 			: true
            , ifFormat          : "%Y-%m-%d"
            , daFormat          : "%Y-%m-%d"
            , date				: "<?php if (empty($GLOBALS["start"])) print str_replace('-', '/', $GLOBALS["todayshort"]); else print str_replace('-', '/', $GLOBALS["start_slash"]); ?>"
            });
            //]]></script>
           </td>
          <td valign="top">
            <strong>End Date</strong><br>
            <div style=" margin: 1em 1em 0 0" id="calendar2"></div>
            <br />
            <script type="text/javascript">//<![CDATA[
            function flatCalendarCallback(cal) {
              if (cal.dateClicked) {
            document.getElementById('end').value=cal.date.print("%Y-%m-%d");
              }
            };
            //]]></script>

            <script type="text/javascript">//<![CDATA[
            Zapatec.Calendar.setup({
              step              : 1,
              flat              : "calendar2",
              flatCallback      : flatCalendarCallback
            , electric          : false
            , noHelp 			: true
            , ifFormat          : "%Y-%m-%d"
            , daFormat          : "%Y-%m-%d"
            , date				: "<?php if (empty($GLOBALS["end"])) print str_replace('-', '/', $GLOBALS["todayshort"]); else print str_replace('-', '/', $GLOBALS["end_slash"]); ?>"
            });
            //]]></script>

          </td>
      </tr>
      <tr>
        <td align="center"><?php if(isset($_SESSION['start'])) print '<font size=5>&raquo;</font>'; ?><input size='15' type="text" id="start" name="start" value="<?php if (empty($GLOBALS["start"])) print str_replace('/', '-', $GLOBALS["todayshort"]); else print str_replace('/', '-', $GLOBALS["start_slash"]); ?>" /></td>
        <td align="center"><?php if(isset($_SESSION['start'])) print '<font size=5>&raquo;</font>'; ?><input  size='15' type="text" id="end" name="end" value="<?php if (empty($GLOBALS["end"])) print str_replace('/', '-', $GLOBALS["todayshort"]); else print str_replace('/', '-', $GLOBALS["end_slash"]); ?>" /></td>
      </tr>
      <tr>
      	<td colspan=2 align="center">
		  <input style=" width: 200px" type="submit" name="set_date_range" value="Set Date">
        </td>
          </form>
      </tr>
      <tr>
        <td colspan=2 align="center">
		  <form action='' method='POST'>
		  <input style=" width: 200px" type="submit" name="clear_date_range" value="Clear">
          </form>
        </td>
      </tr>
      <?php if(isset($_SESSION['start']) or isset($_SESSION['end'])) print "
      <tr>
        <td colspan=2 align='center'>
		  <font style=' margin: 5px;
		  padding: 2px;
		  font-size : 12px;
		  text-transform: uppercase;
	border-color:red;
	border-style:solid;
	border-width: 2px;
    width: 250px;  ' color=red><strong>All reports are using these dates.</strong></font>
        </td>
      </tr>
	  "; ?>
</table>
<?php //print($_SESSION['start'] ." ". $_SESSION['end']); ?>
</div>