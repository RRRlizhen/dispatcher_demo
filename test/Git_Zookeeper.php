<?php session_start();
	session_destroy();
?>
<html>
<body>
	<p>test.php: welcome to visit dispatcher server</p>
</body>
</html>

<?php
/*
 * @return userIp
 * */
function get_real_ip(){
    //TODO
}


class Zookeeper_D
{
	/**
	 * @var Zookeeper
	 */
	private $zookeeper;
    /**
      * @var Callback container
      */
    private $callback = array();
	/**
	 * Constructor
	 *
	 * @param string $address CSV list of host:port values (e.g. "host1:2181,host2:2181")
	 */
	public function __construct($address) {
		$this->zookeeper = new Zookeeper($address);
	}

	
	public function set($path, $value) {
		if (!$this->zookeeper->exists($path)) {
			$this->makePath($path);
			$this->makeNode($path, $value);
		} else {
			$this->zookeeper->set($path, $value);
		}
	}

	
	public function makePath($path, $value = '') {
		$parts = explode('/', $path);
		$parts = array_filter($parts);
		$subpath = '';
		while (count($parts) > 1) {
			$subpath .= '/' . array_shift($parts);
			if (!$this->zookeeper->exists($subpath)) {
				$this->makeNode($subpath, $value);
			}
		}
	}

	
	public function makeNode($path, $value, array $params = array()) {
		if (empty($params)) {
			$params = array(
				array(
					'perms'  => Zookeeper::PERM_ALL,
					'scheme' => 'world',
					'id'     => 'anyone',
				)
			);
		}
		return $this->zookeeper->create($path, $value, $params);
	}

	
	public function get($path) {
		if (!$this->zookeeper->exists($path)) {
			return null;
		}
		return $this->zookeeper->get($path);
	}

	
	public function getChildren($path) {
		if (strlen($path) > 1 && preg_match('@/$@', $path)) {
			// remove trailing /
			$path = substr($path, 0, -1);
		}
		return $this->zookeeper->getChildren($path);
	}
	

	
	 
	 public function deleteNode($path)
	 {
	 	if(!$this->zookeeper->exists($path))
	 	{
	 		return null;
	 	}
	 	else
	 	{
	 		return $this->zookeeper->delete($path);
	 	}
	 }
     
    /**
	 * Wath a given path
	 * @param string $path the path to node
	 * @param callable $callback callback function
	 * @return string|null
	 */
	public function watch($path, $callback)
	{
		//echo "\r\nfunc watch-B  \r\n";
		//var_dump($this->callback);
		//echo "func watch-E  \r\n";
		if (!is_callable($callback)) {
			return null;
		}
		
		if ($this->zookeeper->exists($path)) {
			if (!isset($this->callback[$path])) {
				$this->callback[$path] = array();
			}
			if (!in_array($callback, $this->callback[$path])) {
				$this->callback[$path][] = $callback;
				return $this->zookeeper->get($path, array($this, 'watchCallback'));
			}
		}
	}
	
	/**
	 * Wath event callback warper
	 * @param int $event_type
	 * @param int $stat
	 * @param string $path
	 * @return the return of the callback or null
	 */
	public function watchCallback($event_type, $stat, $path)
	{
		echo "\r\nfunc watchCallback-B  \r\n";
		var_dump($this->callback);
		echo "func watchCallback-E  \r\n";
		if (!isset($this->callback[$path])) {
			return null;
		}
		
		foreach ($this->callback[$path] as $callback) {
			$this->zookeeper->get($path, array($this, 'watchCallback'));
			return call_user_func($callback);
		}
	}
	
	
	/**
	 * Delete watch callback on a node, delete all callback when $callback is null
	 * @param string $path
	 * @param callable $callback
	 * @return boolean|NULL
	 */
	public function cancelWatch($path, $callback = null)
	{
		if (isset($this->callback[$path])) {
			if (empty($callback)) {
				unset($this->callback[$path]);
				$this->zookeeper->get($path); //reset the callback
				return true;
			} else {
				$key = array_search($callback, $this->callback[$path]);
				if ($key !== false) {
					unset($this->callback[$path][$key]);
					return true;
				} else {
					return null;
				}
			}
		} else {
			return null;
		}
    }//function

    function callback(){
        echo "in watch callback\n";
        
    }
}
//============
$signal_server = array();
$media_server = array();
$record_server = array();

$zk = new Zookeeper_D('localhost:2181');

var_dump($zk->get('/')); 
var_dump($zk->getChildren('/'));
echo "=============\r\n";
function xxxcallback(){
	echo "in watch callback\n";
}

$zk->set('/test', 888);
$ret = $zk->watch('/test',array($zk,'Callback'));
$_SESSION['signal_server'] = $signal_server;
$_SESSION['signal_server']['sig_1'] = $zk->watch('/signal_server/sig_1',array($zk,'Callback'));
var_dump($_SESSION);


while(true){
	echo ".";
	sleep(1);
}


echo "begin====\r\n";
function foo(){
    $arr = array("singal_server","media_server","record_server");
    foreach($arr as $i){
        echo $i."<br>\r\n";
    }
    $which_server = "signal_server";
    do_session($which_server);
}


function do_session($key){
    if(isset($_SESSION[$key])){
        echo "Y\r\n";
        echo var_dump($_SESSION[$key]);
    }else{
        echo "N-$key\r\n";
        $zkclient = new Zookeeper_D('localhost:2181');
        $znode = $zkclient->getChildren('/'.$key);
        $node_znode = array();
        foreach($znode as $item){
            $npath = '/'.$key.'/'.$item;
            echo $npath."\r\n";
        }
    }
}
echo "end======\r\n";

?>