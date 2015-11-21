var num_acep;
var acepcion_parent;
var sublema_last_bold='';
var acepcion_new_mode=-1;

/** Corresponde a la acción de los botones de la sub-barra de Acepción.  
    Carga una acepción existente en las zonas predestinadas para ello.
    Por el momento, se hace uno de la zona "e" que corresponde a la columna izquierda de la pantalla.
 **/
function load_ac(a,o) {
	if(a==0) { 
        /*  Es una nueva acepción. Crear una vacía */
        $.get('ajax.php',{'m':'en','p1':d_id,'p2':'','p3':3,'p4':acepcion_parent,'p5':num_acep+1},function(data) { 
            load_l_keep(l_id);
        });
        return;
    }
    /* En caso contrario, cargar la vista para editar la acepción */    
    a_id=a;
	$.get('ajax.php',{'m':'acld','l':l_id,'sl':sl_id,'e':a_id,'c':a}, function(data) {
		div_e.innerHTML=data.form;		
	});
}

/** Corresponde a la acción del botón de Buscar, en la sub-barra del mismo nombre. 
    Coloca, en la columna izquierda, los términos de cuyas entradas hay resultados de búsqueda.
**/
function do_search(){
    var s =document.getElementById('search');
    if(s.value!="") {
         $.get('ajax.php',{'m':'se', 'p1':s.value},function(data) { 
            div_e.innerHTML="<ul class='menu'>"+data+"</ul>";        
            $(div_p).hide();
        });
    }
}

/** Corresponde a las acciones que están colocadas dentro de la sub-barra de Sublemas, cuyo fin es
    visualizar o crear un sublema nuevo dentro del diccionario correspondiente.    
**/
function load_s(o,obj){
    if(o==0) {
        /* si deseo crear un sublema */
        $.get('ajax.php',{'m':'en','p1':d_id,'p2':'[SUBLEMA_NUEVO]','p3':2,'p4':l_id},function(data) { 
            load_l_keep(data);
        });
        return;
    }
    /* En caso contrario, buscamos cargar los datos del sublema para su edición. */
    $.get('ajax.php',{'m':'suld','d':d_id,'l':l_id,'c':o},function(data) {
        sl_id=o;
        acepcion_new_mode=2; // configuramos el modo de crear acepciòn para que los cree dentro del sublema respectivo
        acepcion_parent=sl_id; // guardamos el identificador del sublema, para crearle acepciones.
        if(data.logged==1) {
            /* Si estamos logueados como usuarios, mostramos las acepciones del sublema */
            a_id=-1;
	    	div_e.innerHTML=data.form;
            var t4=document.getElementById('t4');
            t4.innerHTML=data.acep; // colocamos las acepciones en la barra correspondiente
            num_acep=data.num;
            $(div_p).show();
            $('#ac_new').html("Nueva acepción dentro de sublema");
            acepcion_new_mode=2;
            acepcion_parent=sl_id;
        } else { 
            /* En caso contrario, actualizamos la vista previa del lema */
            div_p.innerHTML=data.preview;
            $(div_p).show();
        }
    });
    
}
/** Está asociado a las acciones de Paremias. Por ahora, la funcionalidad no está activa **/
function load_p(o){
    $('#ac_new').html("Nueva acepción dentro de paremia");
                acepcion_new_mode=4;
     acepcion_parent=p_id;
}

/** Comando de ayuda para cargar un lema y un diccionario a la vez. Utilizado 
    por el sistema de búsqueda cuando se han seleccionada que los resultados deen de tomarse
    de todos los diccionarios en INLEXPO 
**/
function load_d_l(d,a) {
    d_id=d;
    load_l(a);
}

/** Comando que carga los datos de edición de un LEMA, manteniendo el modo actual de creación de acepciones.
    Es decir, no 'resetea' el valor de acepcion_new_mode y si estaba dentro de 'sublema', vuelve a cargar
    los datos de ese sublema.
 **/
function load_l_keep(a) {
    $.get('ajax.php',{'m':'leld','d':d_id,'l':l_id,'c':a},function(data) {
        l_id=a;
        if(data.logged==1) {
            l_active.innerHTML='Lema activo: '+ data.name;             
            a_id=-1;
            //ac_active.innerHTML='Acepción activa: ',data.numentry;
	    	//div_e.innerHTML=data.form;
            var t4=document.getElementById('t4');
            t4.innerHTML=data.acep;
            var t3=document.getElementById('t3');
            t3.innerHTML=data.pare;
            var t2=document.getElementById('t2');
            t2.innerHTML=data.subl;
            num_acep=data.num;
            div_p.innerHTML=data.preview;
            $(div_p).show();
            if(acepcion_new_mode==2){
                load_s(acepcion_parent,null);
            }             
        } else {
            div_p.innerHTML=data.preview;
            $(div_p).show();
        }
    });
}

/** Corresponde a la acción de los botones de la sub-barra de Lema.  
    Carga un lema existente en las zonas predestinadas para ello.
    Por el momento, se hace uno de la zona "e" que corresponde a la columna izquierda de la pantalla 
    y la zona "p" la carga con una vista previa de la entrada completa debidamente formateada 
    según la Planta respectiva
 **/
function load_l(a)
{
    sublema_last_bold='';
    acepcion_new_mode=1;
    acepcion_parent=a;
    if(a==0) {
         /* Creación de lema.  */  
        $.get('ajax.php',{'m':'en','p1':d_id,'p2':'[LEMA_NUEVO]','p3':1,'p4':-1},function(data) { 
            /* cargamos este lema nuevo para ediciòn */
            load_l(data);
        });
        return;
    }    
    /* En caso contrario,  cargamos los datos del lema especificado */
    $.get('ajax.php',{'m':'leld','d':d_id,'l':l_id,'c':a},function(data) {
        l_id=a;
        if(data.logged==1) {
            /* Si estamos logueados, mostrar los sublemas y acepciones de este lema */
            l_active.innerHTML='Lema activo: '+ data.name;             
            a_id=-1;
            //ac_active.innerHTML='Acepción activa: ',data.numentry;
	    	div_e.innerHTML=data.form; // cargamos el formulario de ediciòn
            var t4=document.getElementById('t4');
            t4.innerHTML=data.acep; // cargamos las acepciones
            var t3=document.getElementById('t3');
            //t3.innerHTML=data.pare;
            var t2=document.getElementById('t2');
            t2.innerHTML=data.subl; //cargamos los sublemas
            num_acep=data.num; // guardamos el numero de acepciones hasta el momento 
            div_p.innerHTML=data.preview; // y la vista previa
            $(div_p).show();
            /* Si no hemos activado un sublema, las nuevas acepciones quedan en el lema. Se 
               ajusta acepcion_new_mode y acepcion_parent para reflerar tal caracterìstica */
            $('#ac_new').html("Nueva acepción dentro de lema");
            acepcion_new_mode=1;
            acepcion_parent=l_id;
        } else {
            /* En caso contrario (es decir, visitante publico), se visualiza unicamente
               la vista previa */
            div_p.innerHTML=data.preview;
            $(div_p).show();
        }
    });
}

/** Obtiene todos los diccionarios y se colocan en la sub-barra de 'Diccionarios' **/
function fld(){
    $.get('ajax.php',{'m':'dl'},function(data) { 
        ld.innerHTML="<a onclick='fll(this,-1,'Todos los diccionarios');return false;' href='#' class='bold'>Todos los diccionarios</a>"+data;
        ld.innerHTML+="";
    });
}

/** No se utiliza - Junio 2015 **/
function dodni() {
    var dnt =document.getElementById('dnt');
    if(dnt.value!="") {
        $.get('ajax.php',{'m':'dn','p1':dnt.value},function(data) { 
            fld();
        });
    }
}

/** No se utiliza  - Junio 2015 **/
function dni(){
    var dn =document.getElementById('dn');
    dn.innerHTML="<input type='text' id='dnt'><a onclick='dodni();return false;' href='#'>Crear</a>";
}

/** Se llama cuando se hace clic sobre un diccionario en la sub-barra 'Diccionarios'.  
    Tiene a su cargo el obtener una lista de todos los lemas del diccionario seleccionado en el 
    orden alfabètico tradicional. 
    En una versiòn posterior, se programarà un proceso de paginación para no cargar todos los lemas en 
    una sola petición al servidor.
    
    Los lemas se colocan en la zona "e", de la izquierda, para que la vista previa al seleccionarlos aparezca
    al lado derecho en la zona "p"
 **/
function fll(a,did,dnd) {
    $("#tl a").removeClass('bold');
    $(a).addClass('bold');
    d_id=did; //save dict id
    //dn.innerHTML=dnd;
    $.get('ajax.php',{'m':'ll', 'p1':d_id},function(data) { 
        div_e.innerHTML="<ul class='menu'>"+data+"</ul>";
        //div_e.innerHTML+="<span id='ln'><a href='javascript:lni()'>Nuevo lema</a></span>";
        $(div_p).hide();
    });
}
/** Enlazado a las casillas de selecciòn ùnica de categorìas y marcas.
    Envìa el valor de la casilla seleccionada para su cambio en la base de datos.   
**/
function choose(type, input, ct) {
    if(type==11){
        //update or insert content_int for entry_id=a_id, where content_type=ct
        $.get('ajax.php',{'m':'ur','v':input.value,'t':type,'c':ct,'e':l_id,'a':a_id},function(data) {
            div_p.innerHTML=data;       
        });
    }    
    //$(hid).html($(hid).data('lb')+"...");    
    /*$(hid).next().addClass("fa fa-spinner fa-pulse");
    $.get('ajax.php',{'m':'cco','id':id,'cci':cci,'type':type},function(data) {
        if(data.length<10) {
            //final data. set to save.
            //$(hid).html($(hid).data('lb')+" selected="+cci+" txt="+cv);
            
            $.get('ajax.php',{'m':'uc','c':'t'+id,'v':cci,'t':11,'e':ID,'l':l_id},function(data) {
                div_p.innerHTML=data;                
                $(hid).next().removeClass();
                $(sid).html("");        
            });
            //trigger update content where entry_id=L_ID &  content_type=ID    
        }
        else {
            $(sid).html(data);
        }
    });*/
}

/** Evento asociado a las casillas de ediciòn de texto. Cuando se digita un valor 
   y se sale del campo, este proceso envìa el campo y el dato respectivo al servidor 
   para su actualizaciòn. Se le da retroalimentación al usuario en forma de íconos 
   animados mientras se ejecuta la actualizaciòn y un ìcono de "check" cuando la
   actualizaciòn del dato es permanente y se ha actualizado la vista previa.
**/
function save(type,input,id)
{
    /* Si type=10, los cambios se hacen en la tabla entry */ 
    /* Si type=12, los cambios se hacen en la tabla content */
    $(input).next().addClass("fa fa-spinner fa-pulse");
    $.get('ajax.php',{'m':'u','id':id,'v':input.value,'t':type,'c':input.id,'e':l_id,'a':a_id},function(data) {
        div_p.innerHTML=data;
        $(input).next().removeClass();
        $(input).next().addClass("fa fa-check");        
    });
    //alert("Tipo: "+type+"   Texto:"+input.value+"   ID:"+id);
}
/** Comando de ayuda para ocultar el 'check' de estado cuando vuelvo a editar 
    una casilla que ya habìa modificado **/
function hidei(input) {
    $(input).next().removeClass();
}

/** Comando asociado a la acciòn de trasladarnos de ficha, en la vista de ediciòn 
 de acepciones en la zona que permite modificar la asignaciòn de marcas y categorìas gramaticales **/
function changetab(ev,o)
{
    ev.preventDefault();

    if($(o).hasClass('open')) {
      // do nothing because the link is already open
    } else {
      var oldcontent = $('#sidemenu a.open').attr('href');
      var newcontent = $(o).attr('href');
      
      $(oldcontent).fadeOut('fast', function(){
        $(newcontent).fadeIn().removeClass('hidden');
        $(oldcontent).addClass('hidden');
      });
      
     
      $('#sidemenu a').removeClass('open');
      $(o).addClass('open');
    }
}

/** Permite expandir una sección de la estructura jeràrquica de categorìas gramaticales. 
    Se está en proceso de mejorar la interacciòn del usuario en este punto - Junio 2015 
**/
function expand(o){
  $('#'+o).toggle();
}

/** Código asociado a los botones de exportar presentes en el sistema, en la sub-barra 
 'Exportar entradas'. Segùn el formato deseado, se llama a un PHP aparte en el servidor que
  contiene la lògica detallada del formato respectivo, siguiendo el modelo documentado en la Planta 
**/
function exportar(fmt){
    var t5=document.getElementById('rt5');
    t5.innerHTML="Realizando exportación...";    
    var win=window.open('export.'+fmt+'.php?id='+d_id, '_blank');
    win.focus();
    t5.innerHTML="Exportar entradas";     
}

/** Comando asociado al manejo de usuarios. Visualiza un listado de todos los usuarios
    y permite modificar datos de un usuario, como su correo electrónico, su contraseña
    y su nivel de acceso a INLEXPO.
**/
function listau(){
     $.get('ajax.php',{'m':'ul'},function(data) { 
        div_e.innerHTML="<ul class='menu'>"+data+"</ul>";
        //div_e.innerHTML+="<span id='ln'><a href='javascript:lni()'>Nuevo lema</a></span>";
        $(div_p).hide();
    });    
}

/** Comando asociado al manejo de usuarios. Muestra una pantalla para editar los datos
    de un usuario particular 
**/
function editu(login){
    $.get('ajax.php',{'m':'ue','l':login},function(data) { 
        $(div_p).html(data);
        $(div_p).show();
    });
}

/** Comando asociado al manejo de usuarios. Permite guardar un campo en la base de datos del formulario 
    de edición de usuarios 
**/
function saveu(input,login)
{
    if(input.value=='') return;
    $(input).next().addClass("fa fa-spinner fa-pulse");
    $.get('ajax.php',{'m':'us','l':login,'v':input.value,'c':input.id},function(data) {
        $(input).next().removeClass();
        $(input).next().addClass("fa fa-check");        
    });
}

/** Comando asociado al manejo de usuarios. Permite crear un nuevo usuario con cierto nivel de acceso
    a INLEXPO.
**/
function nuevou(){
    var d=window.prompt("Escribe el login del nuevo usuario");        
    $.get('ajax.php',{'m':'ue','l':d},function(data) { 
        $.get('ajax.php',{'m':'ul'},function(data) { 
           div_e.innerHTML="<ul class='menu'>"+data+"</ul>";
        });                
        $(div_p).html(data);
        $(div_p).show();
    });
            
}
