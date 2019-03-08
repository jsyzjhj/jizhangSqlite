<?php
class CreateSqlite
{
    private $file;
    private $sqLiteOb;
 
    public function __construct($filename)
    {
        $dir = '../data';
        if (!is_dir($dir)){
            mkdir($dir);
        }
		if(empty($filename)){$filename = md5(time());}
        $file = $dir.'/'.$filename;
        if (!file_exists($file)){
            $fp = fopen($file, 'w');
            if (!$fp){
                throw new \Exception('文件'.$dir.'创建失败');
            }
            fclose($fp);
        }
        $this->file = $file;
        $this->sqLiteOb = new \SQLite3($file);
    }
 
    function __destruct(){
        $this->sqLiteOb->close();
    }
 
    function createTable($sql){
		$ret = $this->sqLiteOb->exec($sql);		
        if(!$ret){
            throw new \Exception($this->sqLiteOb->lastErrorMsg());
        }
    }
	
	function query($sql){
		return $this->sqLiteOb->exec($sql);
	}
	
	function get_list($sql){
		$recordlist=array();
		foreach($this->query($sql) as $rstmp){
			$recordlist[]=$rstmp;
		}
		return $recordlist;
	}
	
	function Execute($sql){
		return $this->query($sql)->fetch();
	}
	
	function RecordArray($sql){
		return $this->query($sql)->fetchAll();
	}

	function RecordCount($sql){
		return count($this->RecordArray($sql));
	}

	function RecordLastID(){
		return $this->connection->lastInsertId();
	}
 
}