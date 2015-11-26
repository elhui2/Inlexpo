<?php
/*** Este código genera una versiòn en Microsoft Word 2007 del diccionario.
     Se siguen las mismas reglas que el subproceso de generaciòn de vistas previas
     descrito en el archivo ajax.php, salvo que se ejecuta para todos los lemas
     pertenecientes al diccionario suministrado.
 */
require_once 'vendor/phpoffice/phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

function _docx_lemma($run,$mysqli,$l){
    $lastcat="";$cat="";
    $result = $mysqli->query("SELECT * from entry where id=$l");
    $row=$result->fetch_assoc();
    $result->free();
    $run->addText(htmlspecialchars($row['head']),'BoldText');
    $run->addText(' ');
    _out_aceps_docx($run,$mysqli,$l,0);
    $rt = $mysqli->query("SELECT * from entry where parent=$l and type=2");        
    while($row=$rt->fetch_assoc()) {
        $run->addText(' &#x25a0; '.$row['head']." ",'BoldText');;
        _out_aceps_docx($run,$mysqli,$row['id'],1); 
    }
      
}
function _out_aceps_docx($run,$mysqli,$l,$sublema){
    $result = $mysqli->query("SELECT * from entry where parent=$l and type=3");
    $num=1;
    while($row=$result->fetch_assoc()) {
        $tt=$row['id'];
        $num=$row['number'];
        $cq="SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type=5";
        if($sublema==1) {$cq="SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type=18";}
        //enumerar la categoria.
       $r1 = $mysqli->query($cq);
       $ro1=$r1->fetch_array();
       $r1->free();
       if($ro1==false) {
                //ver si tiene subcategoria
            
            if($sublema==0) {    
                $r21 = $mysqli->query("SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content on content_choice_id = content_int WHERE entry_id =$tt and content_type=5 and content_type_id=6");
            //$html.="**SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content on content_choice_id = content_int WHERE entry_id =$tt and content_type_id=6**";
                $ro1=$r21->fetch_array();
                $r21->free();
            }
            if($ro1==false) {                
                $ro1=array("");
            }       
        }
        if(isset($ro1[0])) {$cat=$ro1[0];}
        if(strlen($cat)==0) {
             // si no tiene, ver si hay anterior. Si hay, colocar "", si no, colocar GRIS        
             if(strlen($lastcat)==0) $run->addText(htmlspecialchars('(falta cat. gram.)'),'ItalicText');;
        }        
        else {
            // else (tiene), si es igual, colocar "", sino, colocar "ro" y actualizar lastcat
            if($cat==$lastcat) { }
            else {
                $run->addText(htmlspecialchars($cat),'ItalicText');
                $lastcat=$cat;
            } 
        }
                
        
        $run->addText(htmlspecialchars(' '.$num.' '),'BoldText');
        //obtener las marcas linguisticas
        $r1 = $mysqli->query("SELECT content_choice_options.content_value_abbr FROM content_choice_options INNER JOIN content ON  content_type_id = content_type AND content_choice_id = content_int WHERE entry_id =$tt and content_type_id>=10 and content_type_id<>18 ");
        while($ro1=$r1->fetch_array()) {
            $run->addText(htmlspecialchars($ro1[0]." "),'ItalicText');
        }
        $r1->free();
        // obtener la definicion
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=1");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $run->addText(htmlspecialchars("(acepción vacía)"));
        else  
            $run->addText(htmlspecialchars($ro1['content_text'].' '));  
        //$num++;           
        // obtener el ejemplo
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=2");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
           $run->addText('');
        else  
           $run->addText(htmlspecialchars($ro1['content_text'].' '));  
        //$num++;           
        // obtener sininumos
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=17");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $run->addText(htmlspecialchars(''));
        else  
            $run->addText(htmlspecialchars('['.$ro1['content_text'].'] '));
        // obtener observacion
        $rs1 = $mysqli->query("SELECT * from content where entry_id=$tt and content_type=8");
        $ro1=$rs1->fetch_assoc();
        if($ro1['content_text']=="") 
            $run->addText(htmlspecialchars(''));
        else  {
            $run->addText(htmlspecialchars('Obs.: '),'BoldText');
            $run->addText(htmlspecialchars($ro1['content_text'].' '));
        }
    }
}
function tempnam_sfx($path, $suffix) 
   { 
      do 
      { 
         $file = $path."/".mt_rand().$suffix; 
         $fp = @fopen($file, 'x'); 
      } 
      while(!$fp); 

      fclose($fp); 
      return $file; 
   } 

$doc=tempnam_sfx("/tmp", ".docx"); 

$phpWord = new \PhpOffice\PhpWord\PhpWord();
$phpWord->setDefaultParagraphStyle(
    array(
        'align'      => 'both',
        'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(12),
        'spacing'    => 120,
    )
);
$phpWord->addParagraphStyle('pStyle', array('spacing' => 100));
$phpWord->addFontStyle('BoldText', array('bold' => true));
$phpWord->addFontStyle('ItalicText', array('italic' => true));

$section = $phpWord->addSection(
    array(
        'colsNum'   => 2,
        'colsSpace' => 360,
        'breakType' => 'continuous',
    )
);

$mysqli = new mysqli("localhost", "inlexpo", "inlexpo", "******");
$mysqli->set_charset("utf8");

    $p1=$mysqli->real_escape_string($_GET['id']);  //id de diccionario
    $result = $mysqli->query("SELECT id from entry where d_id=$p1 and parent=-1 order by head asc");        
    while($row=$result->fetch_assoc()) {
        $textrun = $section->addTextRun('pStyle');
        echo _docx_lemma($textrun,$mysqli,$row['id']);
        //$section->addTextBreak();
    }
   




$phpWord->save($doc,"Word2007");

header('Pragma: no-cache');
// Mark file as already expired for cache; mark with RFC 1123 Date Format up to
// 1 year ahead for caching (ex. Thu, 01 Dec 1994 16:00:00 GMT)
header('Expires: 0');
// Forces cache to re-validate with server
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
// DocX Content Type
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
// Tells browser we are sending file
header('Content-Disposition: attachment; filename=Entradas_Exportadas.docx;');
// Tell proxies and gateways method of file transfer
header('Content-Transfer-Encoding: binary');
// Indicates the size to receiving browser
header('Content-Length: '.filesize($doc));
// Send the file:
readfile($doc);
// Delete the file if you so choose. BE CAREFULE; YOU MAY NEED TO DO THIS
// THROUGH YOUR FRAMEWORK:
unlink($doc);
// End the session. BE CAREFUL; YOU NEED TO DO THIS THROUGH YOUR FRAMEWORK:

?>
