// JavaScript Document

//***** User & authentiation *******

function doLogin(form){
	if (form.user.value === "") {
		alert("Please enter a username!");
		form.user.focus();
		return false;
	} else if (form.pwd.value === "") {
		alert("Please enter a password!");
		form.pwd.focus();
		return false;
	}
	form.do.value = "auth_login";
	form.action = window.location.href;
	return true;
}

function doLogout(form){
	
	form.do.value = "logout";
	form.action = window.location.href;
	return true;
}


//***** UL funcitons *****

function select_ul(sel) {

	//$("#data_list_div").load("ul_columns.php?ul="+sel.options[sel.selectedIndex].value);

	var form = sel.form;
	var val = sel.options[sel.selectedIndex].value;
	if (val === "-") {
		return;
	}

	form.pg.value = "ul_data";
	form.act.value = "select_ul";
	form.ul.value = val;
	form.submit();

}


function selAllULColumns(){
	//Markera alla kolumner
	var cb = document.getElementById("selAllULColCB");
	if(cb.checked){
		$(".ul_columns_li").removeClass("ul_columns_li_unsel");
		$(".ul_columns_li").addClass("ul_columns_li_sel");
		cb.onclick=deselAllULColumns;
		//var lab = document.getElementById("selColCbLab");
		//alert(lab.innerHTML);
		//alert($("#selColCbLab").text());
		$("#selColCbLab").html("Deselect all");
		//alert($("#selColCbLab").text());
	}
/*
	else{
		//Avmarkera alla kolumner
		$(".ul_columns_li").removeClass("ul_columns_li_sel");
		$(".ul_columns_li").addClass("ul_columns_li_unsel");
	}
*/
}

function deselAllULColumns(){
	//Avmarkera alla kolumner
	var cb = document.getElementById("selAllULColCB");
	if(!cb.checked){
		$(".ul_columns_li").removeClass("ul_columns_li_sel");
		$(".ul_columns_li").addClass("ul_columns_li_unsel");
		cb.onclick=selAllULColumns;
		$("#selColCbLab").html("Select all");
	}
}

function showULData(form) {

	var colids = "";
	var cid;
	$(".ul_columns_li_sel").each(function (ix, li) {
		cid = li.id.substr(14);
		if (ix > 0) {
			colids += ";";
		}
		colids += cid;
	});
	//alert(colids);
	if (colids === "") {
		alert("Select columns!");
		return;
	}

	var url = "ul_data_columns.php?ul=" + form.ul.value + "&colids=" + colids;


	if (typeof (form.newulcol) != "undefined") {
		var newcolid = form.newulcol.options[form.newulcol.selectedIndex].value;
		if (newcolid !== "--") {
			url += "&newcolid=" + newcolid
		}
	}
	//alert(url);

	//Load url, with callback function to add editing capability
	//Editing should be put under auth/login check
	$("#ul_data_div").load(url, function (responseText, statusText, xhr) {
         
		 //if(statusText == "success"){     
			//$(".ul_data_cell").bind("click", function(){
				//openEditForCell($(this));
				 
			//});
		 //}	
		 
     });
}


function openULDataForEdit(){
	
	$(".ul_data_cell").bind("click", function(){	
		openEditForCell($(this));		 
	});
}


function openEditForCell (cell){
	//alert("openEditForCell");
	$(".ul_data_cell_edt").each(function(index, element) {
    	closeEditForCell($(this));
    });
	
	var cell_id = cell.attr("id");
	
	var cell_data_type = cell.attr("data_type");
	
	
	cell.unbind("click");
	cell.addClass("ul_data_cell_edt");
	//cell.html("<input type='text' value='"+$.trim(cell.text())+"' id='editCell' /><input type='button' value='Save' onClick=saveEditCell('"+cell_id+"') /><input type='button' value='Cancel' onClick=cancelEditCell('"+cell_id+"') />");
	cell.html("<input type='text' value='"+$.trim(cell.text())+"' id='editCell' /><A class='ul_data_abutton' href='javascript:saveEditCell(\""+cell_id+"\")'>Save</A><A class='ul_data_abutton' href='javascript:cancelEditCell(\""+cell_id+"\")'>Cancel</A>");	
}

function cancelEditCell(cellid){
	
	closeEditForCell($("#"+cellid));
}


function closeEditForCell (cell){
	
	//alert("h  "+cell.html());
	
	cell.removeClass("ul_data_cell_edt");

	cell.bind("click", function(){
		//openEditForCell($(this));	
		openEditForCell(cell);	 
	});

	cell.html(document.getElementById("editCell").value);
}

function saveEditCell(cellid){

	var i = cellid.indexOf("_");
	var row = cellid.substr(0,i);
	var col = cellid.substr(i+1);
	var val = document.getElementById("editCell").value;
	var url = "ul_data_save.php?r="+row+"&c="+col+"&v="+encodeURI(val);
	//alert(url);
	//alert("'"+val+"'");
/* 
	$("#statusDiv").load(url, function(responseText, statusText, xhr) {
         //alert("do something after save if we want");
		// if(statusText == "success"){     
		 //}	
     });
*/
/* */
	 $.get(url, function(responseText, statusText, xhr) {
         //alert("do something after save if we want");
		// if(statusText == "success"){     
		 //}	
     });
/* */
	closeEditForCell($("#"+cellid));

}



function exportULData(form) {
	var colids = "";
	var cid;
	$(".ul_columns_li_sel").each(function (ix, li) {
		cid = li.id.substr(14);
		if (ix > 0) {
			colids += ";";
		}
		colids += cid;
	});
	//alert(colids);
	if (colids === "") {
		alert("Select columns!");
		return;
	}


	var url = "system/export_xlsx.php?t=ul&ul=" + form.ul.value + "&colids=" + colids;
	//alert(url);
	window.open(url, "ul_export");
	//$.get(url);

}

//***** BGS *******

//Edit section
function editSection(sectionid){
	
	var jqsect = $("#section_"+sectionid);
	var jqspan = $("#sectbut_"+sectionid);
	
	var sectdiv = document.getElementById("section_"+sectionid);
	var sdh = sectdiv.clientHeight;
	//alert(sdh);
	var estimRows = Math.ceil(sdh / 15);

	//Reset previously opened section
	$(".section_button").each(function (index, element) {
		//alert("(this).attr(id)"+$(this).attr("id"));
		if ($(this).attr("id") !== "sectbut_" + sectionid) {
			var thissectid = $(this).attr("id").substr(8);
			$(this).html("<a class=\"button\" href='javascript:editSection(" + thissectid + ")'>Edit</a>");
		}
	});

	$(".sectiondiv").removeClass("sectiondiv_edit");
	//$(".section_button").html("hey");

	jqsect.addClass("sectiondiv_edit");

	//Old way
	//jqsect.attr("contenteditable","true");

	let thetext = (document.body.innerText)
		? sectdiv.innerText
		: sectdiv.innerHTML.replace(/\&lt;br\&gt;/gi, "\n").replace(/(&lt;([^&gt;]+)&gt;)/gi, "");

	//New way with textarea
	jqsect.html("<textarea class='edit_ta' rows='" + estimRows + "' name='sect_ta_" + sectionid + "' id='sect_ta_" + sectionid + "'>" + thetext + "</textarea>");

	//Change edit button to save
	jqspan.html("<a class='button' href='javascript:saveSection(" + sectionid + ")'>Save</a>&nbsp;&nbsp;<a class='button' href='javascript:cancelEditSection(" + sectionid + ")'>Cancel</a>");


}

function cancelEditSection(sectionid){
	/* Unnecessary since I have to reload anyway
	var jqsect = $("#section_"+sectionid);
	var jqspan = $("#sectbut_"+sectionid);
	jqsect.attr("contenteditable","false");
	$(".sectiondiv").removeClass("sectiondiv_edit");
	jqspan.html("[<a href='javascript:editSection("+sectionid+")'>Edit</a>]");
	*/
	location.reload(true);
}

function saveSection(sectionid){
	var jqsect = $("#section_"+sectionid);
	var jqta =  $("#sect_ta_"+sectionid);
	var jqspan = $("#sectbut_" + sectionid);

	var sect_ta = document.getElementById("sect_ta_" + sectionid);

	//jqsect.attr("contenteditable","false");
	//var textToSave = jqsect.text();

	//var textToSave = jqta.text();

	//if (document.body.innerText) {
	//	var textToSave = sect_ta.innerText;
	//} else {
	//   var textToSave = sect_ta.innerHTML.replace(/\&lt;br\&gt;/gi,"\n").replace(/(&lt;([^&gt;]+)&gt;)/gi, "");
	//}

	var textToSave = sect_ta.value;

	//alert("\n"+textToSave+"\n");

	if (textToSave === "") {
		alert("The section can not be empty. You can remove the section if you want");
		return;
	}

	var url = "bgs_data_save.php";
	// Send the data using post
	var jqxhr = $.post(url, {act: 'saveSection', sectid: sectionid, secttext: textToSave});

	jqxhr.done(function (data) {
		//The server handled our post request, data is text from server
		alert(data);
		location.reload(true);
	});
	jqxhr.fail(function (jqxhrobj, txtstatus, errorobj) {
		//This is called if we don't get a OK respons from the server, txtstatus=error
		alert("Error sending request for save: " + errorobj);
	});

	$(".sectiondiv").removeClass("sectiondiv_edit");
	
	jqsect.html(textToSave);

	jqspan.html("<a class='button' href='javascript:editSection("+sectionid+")'>Edit</a>");
}

//Edit head
function editHead(field,docid){
	
	var jqheadspan = $("#head_"+field);
	var jqlinkspann = $("#edit_"+field);
	
	//Reset previously opened section
	$(".edit_head").each(function(index, element) {
		if ($(this).attr("id") !== "edit_" + field) {
			var thisfield = $(this).attr("id").substr(5);
			$(this).html("<a class='button update' href='javascript:editHead(\"" + thisfield + "\"," + docid + ")'>Edit</a>");
		}
	});
	
	$(".headspan").removeClass("headspan_edit");
	//$(".section_button").html("hey");
	
	jqheadspan.addClass("headspan_edit");
	jqheadspan.attr("contenteditable","true");
	//Change edit button to save
	jqlinkspann.html("<a class='button' href='javascript:saveHead(\""+field+"\","+docid+")'>Save</a>&nbsp;&nbsp;<a class='button' href='javascript:cancelEditHead(\""+field+"\","+docid+")'>Cancel</a>");
}

function cancelEditHead(field,docid){
	/*
	var jqheadspan = $("#head_"+field);
	var jqlinkspann = $("#edit_"+field);
	jqheadspan.attr("contenteditable","false");
	$(".headspan").removeClass("headspan_edit");
	jqlinkspann.html("[<a href='javascript:editHead(\""+field+"\","+docid+")'>Edit</a>]");
	*/
	location.reload(true);
}

function saveHead(field, docid) {
	var jqheadspan = $("#head_" + field);
	var jqlinkspann = $("#edit_" + field);

	jqheadspan.attr("contenteditable", "false");
	var textToSave = jqheadspan.text();

	if (textToSave === "") {
		alert("The header field can not be empty.");
		return;
	}

	var url = "bgs_data_save.php";
	// Send the data using post
	var jqxhr = $.post(url, {act: 'saveHead', headerfield: field, headtext: textToSave, did: docid});

	jqxhr.done(function (data) {
		//The server handled our post request, data is text from server
		alert(data);
		location.reload(true);
	});
	jqxhr.fail(function (jqxhrobj, txtstatus, errorobj) {
		//This is called if we don't get a OK respons from the server, txtstatus=error
		alert("Error sending request for save: " + errorobj);
	});

	$(".headspan").removeClass("headspan_edit");
	jqlinkspann.html("<a class='button update' href='javascript:editHead(\""+field+"\","+docid+")'>Edit</a>");
}

function updateChromLoc(form){
	
	form.act.value = "updateChromLoc";
	form.submit();
}

function updateBGN(form){
	
	form.act.value = "updateBGN";
	form.submit();
}

function removeSectionFromDoc(form){
	
	form.act.value = "removeSectionFromDoc";
	form.sid.value = form.remSectSel.options[form.remSectSel.selectedIndex].value;
	//alert(form.sidid.value);
	form.submit();
}

function addSectionToDoc(form){
	
	form.act.value = "addSectionToDoc";
	//Here sid
	form.sid.value = form.addSectSel.options[form.addSectSel.selectedIndex].value;
	//alert(form.sidid.value);
	form.submit();
}

function addNewSectionToDoc(form) {

	if (form.newsection.value === "") {
		alert("Please give a name to the new section!");
		form.newsection.focus();
		return;
	} else {
		form.act.value = "addNewSectionToDoc";
		form.submit();
	}
}

function createNewBGSDoc(form) {

	if (validateNewBGSDoc(form)) {
		form.act.value = "createNewBGSDoc";
		form.docid.value = "";
		form.submit();
	}
}

function validateNewBGSDoc(form) {

	if (form.newbgsnum.value === "") {
		alert("Please give a new BGS number!");
		form.newbgsnum.focus();
		return false;
	} else if (isNaN(form.newbgsnum.value)) {
		alert("The new BGS number should be only digits, no letters!");
		form.newbgsnum.focus();
		return false;
	} else if (form.newlocusname.value == "") {
		alert("Please enter a locus name!");
		form.newlocusname.focus();
		return false;
	} else if (form.newlocussymbol.value == "") {
		alert("Please enter a locus symbol!");
		form.newlocussymbol.focus();
		return false;
	}
	var newBGSnum = parseInt(form.newbgsnum.value);
	form.newbgsnum.value = newBGSnum;
	return true;
}

function delDoc(form,docname){
	if(confirm('Do you really want to delete '+docname+'?')){
		form.act.value = "delDoc";	
		form.pg.value = "bgs_start";
		form.submit();
	}
}

function sectionUp(form){
	form.act.value = "sectionUp";
	form.submit();
}
function sectionDown(form){
	form.act.value = "sectionDown";
	form.submit();
}


function locusUp(form,docid){
	form.act.value = "locusUp";
	form.docid.value = docid;
	form.submit();
}
function locusDown(form,docid){
	form.act.value = "locusDown";
	form.docid.value = docid;
	form.submit();
}


function exportPdf(bgs){
	
	var url = "system/export_pdf.php?bgs="+bgs;
	//alert(url);
	window.open(url,"bgs_export");
	//$.get(url);
	
}

//Show large image
function showLargeImg(url,caption){
	var td = document.getElementById("fullsizeimg");
	var div = document.getElementById("fullsize");
	var img = document.createElement("img");
	img.src = url;
	img.onclick=closeFullsize;
	while(td.hasChildNodes()){
		td.removeChild(td.lastChild);
	}
	td.appendChild(img);
	var td2 = document.getElementById("largeimg_caption");
	if(caption!=""){
		while(td2.hasChildNodes()){
			td2.removeChild(td2.lastChild);
		}
		var capt = document.createTextNode(caption);
		td2.appendChild(capt);
	}
/*	
	var x = parseInt((window.innerWidth/2) - (div.offsetWidth/2));
  var y = parseInt((window.innerHeight/2) - (div.offsetHeight/2)); 
  alert("x:"+x+", y:"+y);             
  div.style.top = y+"px";
  div.style.left = x+"px";
 */
  	div.style.display = "block";

	div.style.visibility="visible";
}

function closeFullsize(){
	var div = document.getElementById("fullsize");
	div.style.visibility="hidden";	
}

function doSearch(form,act){
	form.act.value=act;
	form.submit();	
}

//Edit images functions

function updateImageCaption(form,imgid){
	
	form.act.value="upd_capt";
	form.imgid.value=imgid;
	form.submit();	
}

function updateImageOrder(form){
	
	form.act.value="upd_ord";
	form.submit();	
}

function uploadNewImage(form){
	//Validation
	if(form.newimg.value==""){
		alert("Please choose a file for upload!");
		form.newimg.focus();
		return false;
	}
	
	if(form.newimg_ord.value =="" || isNaN(form.newimg_ord.value)){
		alert("Please give a number for image order!");
		form.newimg_ord.focus();
		return false;
	}
	
	form.act.value = "upl_new";
	
	
	form.submit();
}

function removeImage(form,imgid,imgname){
	if(confirm("Do you really want to delete the image "+imgname+"?")){
		form.act.value = "del_img";
		form.imgid.value=imgid;
		form.submit();
	}
}

//User admin

function updateUser(userid,form){
	//alert("updateUser("+userid+")");
	if(validateUser(userid,form)){
		form.act.value = "updateUser";
		form.userid.value=userid;
		form.submit();
	}
}

function deleteUser(userid,username,form){
	//alert("deleteUser("+userid+")");
	if(confirm("Do you really want to delete the user '"+username+"'?")){
		form.act.value = "deleteUser";
		form.userid.value=userid;
		form.submit();
	}
}

function addUser(form){
	//alert("addUser()");
	if(validateUser("new",form)){
		form.act.value = "addUser";
		form.submit();
	}
}

function changePassword(userid,form){
	
	field = eval("form.password_"+userid);
	if(validatePassword(field)){
		form.act.value = "changePassword";
		form.userid.value=userid;
		form.submit();
	}
}

function validateUser(userid,form){
	var field;
	var prohibchar = new Array(' ','*','<','>','&','"','\'','\n','\t','\\');
	var usernameMin = 3;
	var usernameMax = 20;
	
	if(userid=="new"){ //Validate username only for new user
		//Username
		field = eval("form.username_"+userid);
		for(i=0;i<prohibchar.length;i++){
			if(field.value.indexOf(prohibchar[i]) != -1){
				alert("Your username can not contain the character '"+prohibchar[i]+"'");
				field.focus();
				return false;
			}
		}
		if(field.value.length < usernameMin){
			alert("Your username must be at least "+usernameMin+" characters long.");
			field.focus();
			return false;
		}else if(field.value.length > usernameMax){
			alert("Your username can be maximum "+usernameMax+" characters long.");
			field.focus();
			return false;
		}
	}
	if(userid=="new"){ //Validate password only for new user
		//Password
		field = eval("form.password_"+userid);
		if(!validatePassword(field)){
			return false;
		}
	}
	
	//Real name
	field = eval("form.realname_"+userid);
	if(field.value==""){
		alert("You must enter a real name for this user.");
		field.focus();
		return false;
	}
	for(i=0;i<prohibchar.length;i++){
		if(i!=0 && field.value.indexOf(prohibchar[i]) != -1){ //Allow whitespace in real name
			alert("Your real name can not contain the character '"+prohibchar[i]+"'");
			field.focus();
			return false;
		}
	}
	return true;
}

function validatePassword(field){
	var prohibchar = new Array(' ','*','<','>','&','"','\'','\n','\t','\\');
	var passwordMin = 8;
	var passwordMax = 20;
	
	if(field.value=="Choose password"){
		alert("Please enter a password for the user!");
		field.focus();
		return false;
	}
	
	for(i=0;i<prohibchar.length;i++){
		if(field.value.indexOf(prohibchar[i]) != -1){
			alert("Your password can not contain the character '"+prohibchar[i]+"'");
			field.focus();
			return false;
		}
	}
	if(field.value.length < passwordMin){
		alert("Your password must be at least "+passwordMin+" characters long.");
		field.focus();
		return false;
	}else if(field.value.length > passwordMax){
		alert("Your password can be maximum "+passwordMax+" characters long.");
		field.focus();
		return false;
	}
	return true;
}






