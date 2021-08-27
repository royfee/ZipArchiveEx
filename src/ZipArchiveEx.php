<?php
/*
	ZipArchive �ļ�����չ��
*/

class ZipArchiveEx extends \ZipArchive {
	public $exclude_dir = [];
	/**
	 * �����ļ�
	 * flags Ĭ���ļ����ھ���д���ļ������ھʹ���
	 */
    public function open($filename,$flags = NULL){
        parent::open($filename, $flags ? $flags : \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    }

    /**
     * �ݹ����Ŀ¼
     */
	public function addDir($dirname) {
		return $this->recursiveAddDir($dirname);
	}

    /**
     * ���Զ���ļ�����OSS����ļ�
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
     * ��ȡԶ���ļ�����
     * @param Զ���ļ���
     * @return string  �ļ�����
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
     * �ų�Ŀ¼
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