<?php
date_default_timezone_set('PRC');

if(!defined("DIR_ROOT")){
    define("DIR_ROOT",dirname(dirname(__FILE__)));
}
define('LEVEL_FATAL',0);
define('LEVEL_ERROR',1);
define('LEVEL_WARN',2);
define('LEVEL_INFO',3);
define('LEVEL_DEBUG',4);

/*
 *记录操作过程中的日志信息
 * */
class Logger{
    static $LOG_LEVEL_NAMES = array(
        'FATAL','ERROR','WARN','INFO','DEBUG'
    );
    private $level = LEVEL_DEBUG;
    private $rootDir = DIR_ROOT;
    static function getInstance(){
        return new Logger;
    }

    //设置最小的log记录级别，小于该级别的log日志输出将被忽略掉
    //@param int $lvl -- 最小的log日志输出级别
    //@throws Exception
    function setLogLevel($lvl){
        if($lvl >= count(Logger::$LOG_LEVEL_NAMES) || $lvl<0){
            throw new Exception('invalid log level:'.$lvl);
        }
        $this->level = $lvl;
    }

    //输出各个级别的日志信息--start
    function debug($message,$name = 'root'){
        $this->_log(LEVEL_DEBUG,$message,$name);
    }
    function info($message,$name = 'root'){
        $this->_log(LEVEL_INFO,$message,$name);
    }

    function warn($message,$name = 'root'){
        $this->_log(LEVEL_WARN,$messag,$name);
    }

    function fatal($message,$name = 'root'){
        $this->_log(LEVEL_FATAL,$message,$name);
    }
    //输出各个级别的日志信息--end


    /*
     *记录log日志信息
     *@param unkonwn_type $level
     *@param unkonwn_type $message
     *@param unkonwn_type $name
     * */
    private function _log($level,$message,$name){
        if($level > $this->level){
            return;
        }
        $log_file_path = $this->rootDir.'/logs/'.$name.'.log';
        $log_level_name = Logger::$LOG_LEVEL_NAMES[$this->level];
        $content = date('Y-m-d H:i:s').' ['.$log_level_name.'] '.$message."\n";
        file_put_contents($log_file_path,$content,FILE_APPEND);
    }
}
//$logger = Logger::getInstance();
//$logger->debug('this is my first log','test');
?>
