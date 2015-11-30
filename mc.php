<?php require 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"> 
<style>
ul {width:40%;display:block;margin:10;}
li {display:block;width:250px;list-style-type:none;}
</style>
<script src="js/jquery-2.1.3.min.js" type="text/javascript"></script>
<script type="text/javascript">
function choose(id,cci,cv) {
    var hid="#h"+id;
    var sid="#s"+id; 
    $(hid).html($(hid).data('lb')+"...");    
    $.get('ajax.php',{'m':'cco','id':id,'cci':cci},function(data) {
        if(data.length<10) {
            //final data. set to save.
            $(hid).html($(hid).data('lb')+" selected="+cci+" txt="+cv);    
        }
        else {
            $(sid).html(data);
        }
    });
}
</script>
</head>
<body>
<?php
$mysqli = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name'], $DB['port'], $DB['sock']);
$mysqli->set_charset("utf8");
$result = $mysqli->query("SELECT * from content_types where parent=-1 and control=2");
while($row=$result->fetch_assoc()) {
    $id=$row['type_id'];
    $lb=$row['label'];
    echo "<h2 id='h$id' data-lb='$lb'>".htmlentities($lb)."</h2>";
    $l1="<ul>";
    $r2 = $mysqli->query("SELECT * from content_choice_options where content_type_id=$id and content_parent_value=-1");
    while($rw1=$r2->fetch_assoc()) {
        $cci=$rw1['content_choice_id'];
        $cv=$rw1['content_value'];
        $ca=$rw1['content_value_abbr'];
        $l1.="<li><a href='javascript:choose($id,$cci,\"$ca\" )'>$cv</a></li>";      
               
    }
    $l1.="</ul><ul id='s$id'></ul>";
    echo "<div id='f$id' class='mc'>".htmlentities($l1)."</div>";
}
    ?>
</body>
</html>
