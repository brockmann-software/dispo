<?php
require_once('/www-dev/ZendFramework-1.12.20/library/Zend/Date.php');
if (isset($_GET['submit'])) {
	$myDate=new Zend_Date();
//	$myDate->set($_GET['delivery_date'].' '.$_GET['delivery_time'], Zend_Date::DATETIME);
	$myDate->set($_GET['delivery_date']);
	$myDate->set($_GET['delivery_time'], Zend_Date::TIME_SHORT);
	echo '<div>'.$myDate->toString('YYYY-MM-dd HH:mm:ss').'</div>';
}
?>
<html>
<head>
</head>
<body>
<script>
function fillZero(wert, stellen)
{
	stellen = stellen || 2;
	while (wert.length<stellen) wert="0"+wert;
	return wert;
}

function checkDate(id_date)
{
	var today = new Date();
	var myDate = document.getElementById(id_date).value;
	var dateArray = myDate.split(/[.,\-\/]+/);
	if ((dateArray[0]!=null) && (dateArray[0]!='')) day=dateArray[0]; else day=today.getDate();
	if (dateArray[1]!=null) month=dateArray[1]; else month=today.getMonth()+1;
	if (dateArray[2]!=null) year=dateArray[2]; else year=today.getFullYear();
	if (day.length ==4) {
		month=myDate.substr(2,2);
		day=myDate.substr(0,2);
	}else if (day.length>=5) {
		year=myDate.substr(4,4);
		if (year.length<3) year=Number(year)+2000;
		month=myDate.substr(2,2);
		day=myDate.substr(0,2);
	}
	document.getElementById(id_date).value=fillZero(day)+"."+fillZero(month)+"."+year;
}

function checkTime(id_time)
{
	var today = new Date();
	var myTime = document.getElementById(id_time).value;
	var timeArray = myTime.split(/[.,\-:\/]+/);
	if ((timeArray[0]!=null) && (timeArray[0]!='')) hours=timeArray[0]; else hours=today.getHours();
	if (timeArray[1]!=null) minutes=timeArray[1]; else minutes=today.getMinutes();
	if (timeArray[2]!=null) seconds=timeArray[2]; else seconds='';
	if ((hours.length>2) && (hours.length<=4)) {
		hours=myTime.substr(0,2);
		minutes=myTime.substr(2,2);
	} else if (hours.length>=5) {
		hours=myTime.substr(0,2);
		minutes=myTime.substr(2,2);
		seconds=myTime.substr(4,2);
	}
	myTime=fillZero(hours)+":"+fillZero(minutes);
	if (seconds!="") myTime=myTime+":"+fillZero(seconds);
	document.getElementById(id_time).value=myTime;
}	
</script>
<form>
<input type="date" name="delivery_date" id="delivery_date" onChange="checkDate('delivery_date')" />
<input type="time" name="delivery_time" id="delivery_time" onChange="checkTime('delivery_time')" />
<input type="submit" name="submit" value="Speichern" />
</form>
</body>
</html>