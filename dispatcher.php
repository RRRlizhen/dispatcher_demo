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
    private $valueTkey = array();
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
	public function watch($path, $callback_)
	{
		echo "\r\nfunc watch-B  \r\n";
		echo "path = $path\r\n";
	//	var_dump($this->callback);
		if (!is_callable($callback_)) {
			return null;
		}
		
		if ($this->zookeeper->exists($path)) {
			if (!isset($this->callback[$path])) {
				$this->callback[$path] = array();
			}
			if (!in_array($callback_, $this->callback[$path])) {
				$this->callback[$path][] = $callback_;
				//var_dump($this->callback);
				//return $this->zookeeper->get($path, array($this, 'watchCallback'));
				return $this->zookeeper->getChildren($path,array($this,'watchCallback_getChildren'));
				//对"/signal_server"这个znode节点，利用watchCallback_getChildren方法进行目录注册，监听其字节点动作
			}
		}
		echo "func watch-E  \r\n";
	}
	
	//
	/**
	  当监听的znode动作发生，需要判断事件发生的类型
	  因为zk中子节点动作类型只有一种，CHILD_EVENT=4，所以可以不需要判断event_type的值（认为只要zk_server发生了改变，就更新_SESSION）
	 */
	public function watchCallback_getChildren($event_type,$stat,$path){
		echo "wC_getChildren-b \r\n";
//		var_dump($this->callback);
		echo "event_type= $event_type \r\n";
		echo "stat= $stat \r\n";
		echo "path= $path \r\n";
		echo "wC_getChildren-e \r\n";
		if(!isset($this->callback[$path])){
			return null;
		}
		//=========
		foreach($this->callback[$path] as $callback){
			$this->zookeeper->getChildren($path,array($this,'watchCallback_getChildren'));//因为监听中用过一次，需要重新注册一次新的监听
			return call_user_func($callback,$path);//对path="/signal_server"，执行前面已经注册函数（callback_children）
		}
	}

	
    function callback($path){
        echo "in watch callback\r\n";
        //echo "$path\r\n";
    }
    
    

	/**当监听节点(signal_server)的字节点发生变化(delete,add两种动作)时，需要对_SESSION数组进行修改
	 * 1，当signal_server添加节点时，如果_SESSION[signal_server][sig1_1.1.1.1_:_port]不存在，那么添加上
	 * 2，当signal_server删除节点时，如果_SESSION[signal_server][sig1_1.1.1.1_:_port]存在，那么去除
	 * ----这里采用做法时，直接清空_SESSION,再从zkServer中拉取
	 */
    function callback_children($path){
    	echo "callback_children-b \r\n";
    	echo "<br>=====$path<br> \r\n";
    	$arr_path = explode('/', $path);
    	$key = $arr_path[1];
    	echo "$key \r\n";
    	if(!isset($this->valueTkey[$key])){
    		$this->valueTkey[$key] = array();
    	}
    	
    	if (!isset($_SESSION[$key])){
    		$_SESSION[$key] = array();
    	}else{
    		unset($_SESSION[$key]);
    		$_SESSION[$key] = array();
    	}
    	$value = $this->getChildren($path);
    	foreach ($value as $item){
    		array_push($_SESSION[$key], $item);
    	}  
    	var_dump($_SESSION);
    	echo "callback_children-e\r\n";
    }
    
}
//============
echo "begin====\r\n";
function foo(){
    $arr = array("singal_server","media_server","record_server");
    foreach($arr as $i){
        echo $i."<br>\r\n";
    }
    $key = "signal_server";
    return do_session($key);//测试简化，只对"/signal_server"的节点目录作测试
}


function do_session($key){
    if(isset($_SESSION[$key])){//判断以signal_server为关键字数组元素是否为空？如果非空可以直接从_SESSION[]中取出对应的value返回
        echo "Y\r\n";
        echo var_dump($_SESSION[$key]);
        return true;
        print "-------";
    }else{//_SESSION[]中不存在以signal_server为关键字的value
        echo "N-$key\r\n";
        $zkclient = new Zookeeper_D('localhost:2181');//连接zkserver服务器
        $value = $zkclient->getChildren('/'.$key);//获取路径等于"/signal_server"时，所有的字节点
        var_dump($value);
        
        $path = '/'.$key;//设置路径path="/signal_server"
        echo $path."\r\n";
        $tmpChildren = $zkclient->watch($path, array($zkclient,'callback_children'));
		//在zkclient中对path路径，注册回调函数callback_children


        if(!isset($_SESSION[$key])){
        	$_SESSION[$key] = array();
        }
        foreach ($tmpChildren as $item){
        	array_push($_SESSION[$key], $item);
        }    
		//if和foreach  初始化_SESSION


        while(true){//阻塞,等待zkServer中znode节点的修改
        	echo ".";
        	sleep(2);
        }
        var_dump($_SESSION[$key]);
    }
    echo "<br>last end<br>\r\n";
    //var_dump($_SESSION);
}
foo();
echo "end======\r\n";
?>
