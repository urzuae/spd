//RECONOCIMIENTO DE NAVEGADOR
var nav;

if(navigator.appName == "Netscape"){
	nav = "otro"
}else{
	nav = "ie";
}

function moneyFormat(howmuch)
{
  var money = howmuch;
  var money2, sign;
  if (money == "" || money == undefined) return "$ 0.00";
  //-$ 555,555,555.55 kitar formato
  money = money.replace(/\$/g, "");
  money = money.replace(/ /g, "");
  money = money.replace(/,/g, "");
  money = money.replace(/[A-Z,a-z]/g, "");
  //-5555555.5555555 recibimos así
  if (money.charAt(0) == "-") //es negativo
  {
    money = money.substr(1); //kitar el signo
    sign = "-";
  }
  else
  sign = "";
  if (money.indexOf(".") >= 0) //tiene decimales
  {
    moneyDec = "" + (Math.round(parseFloat(money.substr(money.indexOf(".")))*100)/100); //redondear
    moneyDec = moneyDec.substr(1); //kitar el 0 inicial de 0.12
    if (moneyDec.length == 2) moneyDec += "0"; //le falta un 0
  }
  else
  {
    moneyDec = ".00";
  }
  var moneyInt;
  if (money.indexOf(".") == -1)
  moneyInt = money; //si no tiene decimales es un entero
  else
  moneyInt = money.substr(0, money.indexOf(".")); //espaciar enteros solamente
  var i = moneyInt.length; //empezar al final
  var moneyCommas = "";
  var comma = "";
  while (1)
  {
    //contar 3 para atras
    if (i - 3 >= 0)
    ss = moneyInt.substr(i - 3, 3);
    else
    ss = moneyInt.substr(0, i); //si es igual a 2 entonces desde el principio tomar 2
    moneyCommas = ss + comma + moneyCommas;
    i = i - 3;
    if (comma == "") comma = ","; //la primera vuelta no necesita comma
    if (i == 0 ) comma = ""; //si es el ultimo no necesita comma
    if (i < 0) break; //ya se paso
  }
  money2 = moneyCommas + moneyDec;
  money2 = sign + "$ " + money2;
  return money2;
}

function init_everything()
{
 var f = document.forms[0];
 if (f)
 {
  var els = f.elements;
  for (i=0; i<els.length; i++)
  {
    //ke onblur se caps
    if (els[i].type == "text" || els[i].type == "textarea")
    {
      if (els[i].name == "email") break;
      if (!els[i].onblur)
      els[i].onblur = function()
      {
        caps1(this);
      }
      ;
      //poner azul si está prellenado
      if (els[i].value != "") els[i].style.color = "#000000";
    }
  }
 }
}
function caps1(obj)
{
  obj.value = obj.value.toUpperCase();
  obj.style.color = "black";
}
function capsall(theForm)
{
  var els = theForm.elements; for(i=0; i<els.length; i++)
  {
    switch(els[i].type)
    {
      case "text":
      if (els[i].name == "email") break;
      els[i].value= els[i].value.toUpperCase();
      break;
      case "textarea":
      els[i].value= els[i].value.toUpperCase();
      break;
    }
  }
}


function check_chars(el, chars)
{
  var s = "";
  var j = 0;
  for (i = 0; i < el.value.length; i++)
  {
    if (chars.indexOf(el.value.charAt(i)) != -1)
    {
      s = s + el.value.charAt(i);
    }
    else j++;
  }
  el.value = s;
  if (j > 0)
  {
   alert('Se eliminaron caracteres no válidos.');
   el.focus();
   return false;
  }
  return true;
}

var lock = "";
function check_min_length(el, min_size)
{
    if (el.value.length > 0)
      if (el.value.length < min_size && (lock == el.name || lock == ""))
      {
          lock = el.name;
          alert("El tamaño minimo para este campo es de "+min_size+" caracteres.");
          el.value = "";
          el.focus();
          return false;
      }
      else
      {
      el.style.color = 'black';
      lock = "";
      return true;
      }
}
function check_min_length_not_null(el, min_size)
{
      if (el.value.length < min_size && (lock == el.name || lock == ""))
      {
          lock = el.name;
          alert("El tamaño minimo para este campo es de "+min_size+" caracteres.");
	  el.value = "";
          el.focus();
          return false;
      }
      else
      {
      el.style.color = 'black';
      lock = "";
      return true;
      }
}


/* FUNCIONES PARA FUENTES */
var xmlHttp, origen_padre_id, baseurl = "index.php?_module=Catalogos&_op=fuentes";

function createXMLHttpRequest(u){
	if (window.ActiveXObject) {
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	else if (window.XMLHttpRequest) {
		return new XMLHttpRequest();
	}
}
function fuentes_add_form(sub_or_padre){
	if(sub_or_padre != ""){
		document.getElementById("div_agregar_nueva_fuente").style.display = "block";
	}
}
function fuentes_add(){
	var nueva_fuente;
	nueva_fuente = document.getElementById("nueva_fuente").value;
	if(nueva_fuente == ""){
		alert("Debe ingresar el nombre de la fuente");
	}
	else{
		xmlHttp = createXMLHttpRequest();
	    var url = baseurl + "&action=insert_sub&origen_padre_id=" + origen_padre_id + "&nueva_fuente="+nueva_fuente+"&rand="+rand(1000);
		xmlHttp.open("GET", url, true);
		xmlHttp.onreadystatechange = function(){
			if(xmlHttp.responseText == "true"){
				alert("Se agregó la fuente "+nueva_fuente);
				fuentes_show_subs(origen_padre_id);
			}
			else{
				alert("Esta fuente ya esta registrada");
			}
		};
		xmlHttp.send(null);
	}
}
function fuentes_cancel(){	
	document.getElementById("nueva_fuente").value = "";
	document.getElementById("div_agregar_nueva_fuente").style.display = "none";
}
function fuentes_show_subs(id){
	origen_padre_id = id; 
	xmlHttp = createXMLHttpRequest();
    var url = baseurl + "&action=show_subs&origen_padre_id=" + origen_padre_id + "&rand="+rand(1000);
	xmlHttp.open("GET", url, true);
	xmlHttp.onreadystatechange = fuentes_show_subs_cb;
	xmlHttp.send(null);
}
function fuentes_show_subs_cb(){
	if (xmlHttp.readyState == 4){
		if (xmlHttp.status == 200){				
    		document.getElementById("html_origen").innerHTML = "";
	    	document.getElementById("html_origen").innerHTML = xmlHttp.responseText;
	    	document.getElementById("div_agregar_nuevo_form_fuente").style.display = "block";
	    }
	}
}

function fuentes_lock_subs(id){
	respuesta = confirm("¿Está seguro que desea bloquear esta fuente?");
	if(respuesta){
		xmlHttp = createXMLHttpRequest();
	    var url = baseurl + "&action=lock_subs&origen_id=" + id;
		xmlHttp.open("GET", url, true);
		xmlHttp.onreadystatechange = fuentes_show_subs_cb;
		xmlHttp.send(null);
	}
}
function fuentes_unlock_subs(id){
	respuesta = confirm("¿Está seguro que desea desbloquear esta fuente?");
	if(respuesta){
		xmlHttp = createXMLHttpRequest();
	    var url = baseurl + "&action=unlock_subs&origen_id=" + id;
		xmlHttp.open("GET", url, true);
		xmlHttp.onreadystatechange = fuentes_show_subs_cb;
		xmlHttp.send(null);
	}
}
function rand(n)
{
  	return ( Math.floor ( Math.random ( ) * n + 1 ) );
}

function update_fecha_fin(cal)
{
  //checamos si es mayor la ini que la fin y cambiar el fin
  var fecha_fin = document.getElementById("fecha_fin").value;
  if (fecha_fin == '') return false;
  var guion_1 = fecha_fin.indexOf("-");
  var guion_2 = fecha_fin.indexOf("-", guion_1 + 1);
  var guion_3 = fecha_fin.length;//fecha_fin.indexOf("-", guion_2 + 1);
  var fin_d = fecha_fin.substring(0, guion_1);
  var fin_m = fecha_fin.substring(guion_1 + 1, guion_2);
  var fin_y = fecha_fin.substring(guion_2 + 1, guion_3);
  var fin  = new Date(fin_y, fin_m - 1, fin_d);
  var ini  = new Date(cal.date.getFullYear(), cal.date.getMonth(), cal.date.getDate());
  if (ini.getTime() > fin.getTime())
  {
   document.getElementById("fecha_fin").value = cal.date.print("%d-%m-%Y");
  }
}

function update_fecha_ini(cal)
{
  //checamos si es mayor la ini que la fin y cambiar el fin
  var fecha_ini = document.getElementById("fecha_ini").value;
  if (fecha_ini == '') return false;
  var guion_1 = fecha_ini.indexOf("-");
  var guion_2 = fecha_ini.indexOf("-", guion_1 + 1);
  var guion_3 = fecha_ini.length;//fecha_ini.indexOf("-", guion_2 + 1);
  var ini_d = fecha_ini.substring(0, guion_1);
  var ini_m = fecha_ini.substring(guion_1 + 1, guion_2);
  var ini_y = fecha_ini.substring(guion_2 + 1, guion_3);
  var ini  = new Date(ini_y, ini_m - 1, ini_d);
  var fin  = new Date(cal.date.getFullYear(), cal.date.getMonth(), cal.date.getDate());
  if (ini.getTime() > fin.getTime())
  {
   document.getElementById("fecha_ini").value = cal.date.print("%d-%m-%Y");
  }
}
