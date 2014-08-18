<html>
<head>
<title>Validate iTunes In App Purchase Receipt Code Online Tool</title>
<meta name="description" value="A tool to allow you to verify iTunes In-App Purchase Receipt Codes against Apple's Servers. PHP Implementation." />

</head>
<body>

<div id="retData" style="float:center; text-align:center; font-family:helvetica,arial; font-size:16px;">
<?php if($_POST['receipt'] != '') { ?>
<br /><br /><br />Validating receipt code:<br /> <?php echo $_POST['receipt'] ?><br /><br /><br /><img src="loading.gif" />
<?php } else { ?>
<br /><br />
<form name="receipttoken" action="verify.php" method="post">
Enter Receipt Token (b64)<br /><br />  <textarea type="text" style="width:300px; height:200px; font-family:helvetica,arial; font-size:16px;" name="receipt"></textarea><br /><br />
Enter Shared Secret<br /><br /> <input type="text" style="width:300px; height:25px; font-family:helvetica,arial; font-size:16px;" name="secret"></input><br /><br />
<input type="submit" value="Validate" />
</form>
<?php } ?>
</div>
</body>
</html> 
