<div id="ftr">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td rowspan="3" class="brdrL">&nbsp;</td>
			<td><img src="/images/s.gif" width="700" height="4" alt=""></td>
			<td rowspan="3" class="brdrR">&nbsp;</td>
		</tr>
		<tr>
			<td id="ftrNav"></td>

		</tr>
		<tr>
			<td class="copy"></td>
		</tr>
		<tr>
			<td><img src="/images/brdr_l_corner.jpg" width="12" height="7" alt=""></td>
			<td class="brdrBtm"><img src="/images/s.gif" width="700" height="7" alt=""></td>
			<td><img src="/images/brdr_r_corner.jpg" width="12" height="7" alt=""></td>

		</tr>
	</table>
</div>
<br />
&nbsp;
</body>

</html>
<?php
$os = strtolower(php_uname ("s"));
if (strpos($os, 'windows') === false) {
	@$db->close();
}
?>