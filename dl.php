<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"> 
<script src="js/jquery-2.1.3.min.js" type="text/javascript"></script>
<script type="text/javascript">
var d_id;
var ld;
var dn;
var ll;
function dolni() {
    var lnt =document.getElementById('lnt');
    if(lnt.value!="") {
        $.get('ajax.php',{'m':'en','p1':d_id,'p2':lnt.value,'p3':1,'p4':-1},function(data) { 
            fll();
        });
    }
}
function lni(){
    var dn =document.getElementById('ln');
    dn.innerHTML="<input type='text' id='lnt'><button onclick='dolni()'>Guardar</button>";
}

function dodni() {
    var dnt =document.getElementById('dnt');
    if(dnt.value!="") {
        $.get('ajax.php',{'m':'dn','p1':dnt.value},function(data) { 
            fld();
        });
    }
}
function dni(){
    var dn =document.getElementById('ld');
    dn.innerHTML="<input type='text' id='dnt'><button onclick='dodni()'>Guardar</button>";
}
function fll(did,dnd) {
    d_id=did; //save dict id
    dn.innerHTML=dnd;
    $.get('ajax.php',{'m':'ll', 'p1':d_id},function(data) { 
        ll.innerHTML=data;
        ll.innerHTML+="<span id='ln'><a href='javascript:lni()'>Nuevo lema</a></span>";
    });
}

function fld(){
    $.get('ajax.php',{'m':'dl'},function(data) { 
        ld.innerHTML=data;
        ld.innerHTML+="<span id='dn'><a href='javascript:dni()'>Nuevo diccionario</a></span>";
    });
}
$(document).ready(function() {
    //fill list of dicts.
    ld=document.getElementById('ld');
    dn=document.getElementById('dn');
    ll=document.getElementById('ll');
    fld();
});
</script>
</head>
<body>
<h1> Lista de diccionarios</h2>
<ul id="ld"></ul>
<h2 id="dn"></h2>
<ul id="ll"></h2>
</body>
</html>
