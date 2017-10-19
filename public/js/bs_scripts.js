function openCert(id) {
    path = '/certificate/index/spl/'+id;
		uri = path;
		myWindow = window.open(uri,'Zertifikate',
		                       'width=1024,height=500,left=0,top=100,resizable=yes,scrollbars=yes');
		myWindow.focus;
}

function openCertWin(path) {
    myWindow = window.open(path, 'certificate',
		                       'width=1024,height=750,left=0,top=0,resizable=yes,scrollbars=yes');
}

function addProduct(itemSelectId, selectId) {
    dataProductSelection = document.getElementById(itemSelectId).options;
	productSelection = document.getElementById(selectId).options;
	action = false;
	i = 0;
	while (productSelection[i]) {
		if (productSelection[i].selected==true) {
			newDataProduct = document.createElement('option');
			newDataProduct.label = productSelection[i].label;
			newDataProduct.text = productSelection[i].text;
			newDataProduct.value = productSelection[i].value;
			newDataProduct.selected = true;
			try {
				dataProductSelection.add(newDataProduct, null);
			}
			catch(e) {
				dataProductSelection.add(newDataProduct);
			}
			productSelection[i] = null;
			action = true;
		} else {
			i++;
		}
	}
}

function buildSelect(name, optionArray, selection)
{
	if (optionArray.length == 0) return null;
	myselect = document.createElement('select');
	myselect.name = name;
	myselect.id = name;
	for (var o = 0; o<optionArray.length; o++) {
		var myOption = document.createElement('option');
		myOption.text = optionArray[o].text;
		myOption.value = optionArray[0].value;
		myselect.add(myOption);
	}
	return myselect;
}
	

function deleteProduct(itemSelectId, selectId) {
    dataProductSelection = document.getElementById(itemSelectId).options;
	productSelection = document.getElementById(selectId).options;
	action = false;
	i = 0;
	while (dataProductSelection[i]) {
		if (dataProductSelection[i].selected==true) {
			newDataProduct = document.createElement('option');
			newDataProduct.label = dataProductSelection[i].label;
			newDataProduct.text = dataProductSelection[i].text;
			newDataProduct.value = dataProductSelection[i].value;
			newDataProduct.selected = true;
			try {
				productSelection.add(newDataProduct, null);
			} catch(e) {
				productSelection.add(newDataProduct);
			}
			dataProductSelection[i] = null;
			action = true;
		} else {
			i++;
		}
	};
}

function dataProductSelectAll() {
    dataProductSelection = document.getElementById('dataProducts');
		for (i=0; i<dataProductSelection.length; i++) dataProductSelection[i].selected = true;
		dataBrandSelection = document.getElementById('dataBrands');
		for (i=0; i<dataBrandSelection.length; i++) dataBrandSelection[i].selected = true;
}

var http = null;

function createRequest() {
    if (window.XMLHttpRequest) {
        http = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        http = new ActiveXObject("Microsoft.XMLHTTP");
    }
}

function sendRequest(method, URI, params, reglistener, mode) {
	if (http == null) createRequest();
	if ((http.readyState == 1) || (http.readyState == 2)) {
		alert('Ein geöffneter Request wurde noch nicht abgeschlossen und wird jetzt beendet');
		http.abort();
	}
    http.open(method,URI);
	http.async = mode;
	http.onreadystatechange = reglistener;
	http.setRequestHeader(
      "Content-Type",
      "application/x-www-form-urlencoded");
	http.send(params);
}

function ausgeben() {
   if (http.readyState == 4) {
       var daten = http.responseXML;
			 var ausgabe = document.getElementById("ausgabe");
/*
			 var text = http.responseText;
			 ausgabe.innerHTML = text;
*/			 
			 var ergebnisse = daten.getElementsByTagName("certificate");
			 for (var i=0; i< ergebnisse.length; i++) {
			     var name, product, validy_date;
			     var datum = ergebnisse[i];
					 for (var j=0; j<datum.childNodes.length; j++) {
					     with (datum.childNodes[j]) {
						       if (nodeName == "name") {
									     name = firstChild.nodeValue;
									 } else if (nodeName == "product") {
									     product = firstChild.nodeValue;
									 } else if (nodeName == "validy_date") {
									     validy_date = firstChild.nodeValue;
									 }
						 }
					 }
					 ausgabe.innerHTML = ausgabe.innerHTML + name +'|'+ product +'|'+ validy_date+'<br />'; 
			 }
   }
}

function fillZero(wert, stellen)
{
	stellen = stellen || 2;
	while (String(wert).length<stellen) wert="0"+wert;
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

function getXMLTagValue(XMLNode, tag)
{
	return (XMLNode.getElementsByTagName(tag)[0].firstChild) ? XMLNode.getElementsByTagName(tag)[0].firstChild.nodeValue : false;
}