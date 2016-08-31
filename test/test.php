<?php
class ZookeeperDemo extends Zookeeper{
    public function watcher($i,$type,$key){
        echo "Insider Watch\n";

        $this->get('/signal_server/sig_1',array($this,'watcher'));
    }
}

$zoo = new ZookeeperDemo('127.0.0.1:2181');
//$zoo->get('/test',array($zoo,'watcher'));
$zoo->get('/signal_server/sig_1',array($zoo,'watcher'));
//===================
$path = "/a/b/c/d";
$parts = explode('/',$path);
$parts = array_filter($parts);

while(count($parts)>1){
    $subpath .= '/' . array_shift($parts);
    echo $subpath."\r\n";
}
//==================
$var = '';
if(isset($var)){
    echo "this var is set so i will print \r\n";
}

$a = "test";
$b = "anothertest";

var_dump(isset($a));
var_dump(isset($a,$b));

unset($a);
var_dump(isset($a));
var_dump(isset($a,$b));

$foo = NULL;
var_dump(isset($foo));

echo "====\r\n";
function someFunction(){
}

$functionVariable = 'someFunction';
var_dump(is_callable($functionVariable,false,$callable_name));
echo $callable_name,"\n";

class someClass{
    function someMethod(){

    }
}

$anObject = new someClass();
$methodVariable = array($anObject,'someMethod');
var_dump(is_callable($methodVariable,true,$callable_name));
echo $callable_name,"\n";//someClass::someMethod

echo "===========\r\n";
$os = array("mac","ccxx","linux","NT");
if(in_array("Linux",$os)){
    echo "Go Linux\r\n";
}else{
    echo "no Linux\r\n";
}

$total = array();
$signal_server = array();
$media_server = array();
$record_server = array();

$signal_server['sig_1'] = '1.1.1.1';
$signal_server['sig_2'] = '2.2.2.2';
$signal_server['sig_3'] = '3.3.3.3';
$total['signal_server'] = $signal_server;

$media_server['media_1'] = '1.1.1.1';
$media_server['media_2'] = '5.5.5.5';
$media_server['media_3'] = '6.6.6.6';
$total['media_server'] = $media_server;

$record_server['rec_1'] = '4,4,4,4';
$record_server['rec_2'] = '7,7,7,7';
$record_server['rec_3'] = '8.8.8.8';
$total['record_server'] = $record_server;

if(!in_array('sig_4',$total['signal_server'])){
    echo "no sig_4 in signal_server\r\n";
    $total['signal_server']['sig_4'] = '0.0.0.0';
}

$str_json = json_encode($total);
//==================
unset($total['signal_server']['sig_1']);
//-
$aa = array();
$aa["sig"] = array();
$aa["sig"][] = "/sig/1.1.1";
$aa["sig"][] = "/sig/2.2.2";
var_dump($aa);
//----------
$str = '/signal_server';
$arr_str = explode('/',$str);
var_dump($arr_str);
?>
