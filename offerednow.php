<?php
/**
 * Get data from sx25
 * Author: Diana DeVargas 
 * Create Date: 2012-01-06
 * Update Date:
**/
include_once("config.php");
require_once('header.php');
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
<tbody>
	<tr>
		<td valign="top" align="left"><span class="txtHdr">Test OfferedNow Feed</span></td>	
	</tr>
	<tr>
	<td valign="top" colspan="2">

		<form enctype="multipart/form-data" action="http://www.offerednow.com/search/result/0/laptop" method="POST" id="data_table" name="generate">
		<div id="tableData">
		<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
			<tbody>
			<tr>
				<td align="left" class="cellHdr"><strong>Test</strong></td>
			</tr>
			<tr class="alter1">
				<td valign="middle" align="center">	 
	        	Select ID :  
                   	<select class="inputSelect" name="ID" >
                   	<option value="0" selected="selected">offerednow1google_1</option>
                   	<option value="1" >offerednow1google_2</option>
                   	<option value="2" >offerednow1google_3</option>
                   	<option value="3" >offerednow1google_4</option>
                   	<option value="4" >offerednow1google_5</option>
                   	<option value="5" >offerednow1yahoo_1</option>
                   	<option value="6" >offerednow1yahoo_2</option>
                   	<option value="7" >offerednow1yahoo_3</option>
                   	<option value="8" >offerednow1yahoo_4</option>
                   	<option value="9" >offerednow1yahoo_5</option>
                   	<option value="10" >offerednow1bing_1</option>
                   	<option value="11" >offerednow1bing_2</option>
                   	<option value="12" >offerednow1bing_3</option>
                   	<option value="13" >offerednow1bing_4</option>
                   	<option value="14" >offerednow1bing_5</option>
                	</select>
	        	</td>
			</tr>
			<tr class="alter1">
				<td valign="middle" align="center">	 
					<br>Keyword:
					<input size="50" name="keyword" value="laptop" onchange="$('#data_table').attr('action','http://www.offerednow.com/search/result/0/' + this.value) " />
				</td>
			</tr>
			<tr class="alter2"><td align="center"><input type="submit" value="Submit" name="submit"/></td></tr>
			</tbody>
		</table>
		</div>
		</form>

		<br><br><br><br>

	</td>
	</tr>
</tbody>
</table>		
			
			<!-- *** END MAIN CONTENTS  *** -->
			
					
			</td>
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>
<?php 
	require_once('footer.php');
?>
