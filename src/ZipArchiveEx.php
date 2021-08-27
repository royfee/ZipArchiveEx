<?php
	namespace royfee\zip;

/*
	ZipArchive 文件的扩展包
*/

class ZipArchiveEx extends \ZipArchive {
	public $exclude_dir = [];
	/**
	 * 创建文件
	 * flags 默认文件存在就重写，文件不存在就创建
	 */
    public function open($filename,$flags = NULL){
        parent::open($filename, $flags ? $flags : \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    }

    /**
     * 递归添加目录
     */
	public function addDir($dirname) {
		return $this->recursiveAddDir($dirname);
	}

    /**
     * 添加远程文件比如OSS里的文件
     * $files  array|string
     */
    public function addRemoteFiles($files){
        $fileArray = is_string($files)?[$files]:$files;
        foreach($fileArray as $k => $file){
            $fname = basename($file);
            if(!is_numeric($k)){
                $fname = $k;
            }
            $this->addFromString($fname,$this->getRemoteContents($file));
        }
    }

    /**
     * 获取远程文件内容
     * @param 远程文件名
     * @return string  文件内容
     */
    public function getRemoteContents($remotefile){
        $k = 0;
        while($k++ < 3){
            $content = file_get_contents($remotefile);
            if($content!==false){
                return $content;
            }
        }
        return '';
    }

    /**
	 * Function: excludeDir
     * 排除目录
	 */
	public function excludeDir($dirname){
		if(substr($dirname, -1) == '/'){
			$dirname = substr ($dirname, 0, -1);
		}

		if (!$this->exclude_dir) {
			$this->exclude_dir = [];
		}

		$this->exclude_dir[] = $dirname;
	}

	/**
	 * Function: addDirContents
	 */
	public function addDirContents($dirname) {
		return $this->recursiveAddDir($dirname, null, false);
	}

	/**
	 * Function: recursiveAddDir
	 */
	private function recursiveAddDir($dirname,$basedir=null,$adddir=true)
	{ 
		$rc = false;
		$basename_exclude = $basedir . basename($dirname);

        # If $dirname is a directory and not is exclude dir
		if (is_dir($dirname) && !in_array($basename_exclude, $this->exclude_dir))
		{
			$working_directory = getcwd();
			chdir($dirname);
			$basename = $basedir . basename($dirname);
			if ($adddir) {
				$rc = $this->addEmptyDir($basename);
				$basename = $basename . '/';
			} else {
				$basename = null;
			}
			$files = glob('*');
			foreach ($files as $f) {
				if (is_dir($f)) {
					$this->recursiveAddDir($f, $basename);
				} else {
					$rc = $this->addFile($f, $basename . $f);
				}
			}
			chdir($working_directory);
			$rc = true;
		}
		return $rc;
	}
}