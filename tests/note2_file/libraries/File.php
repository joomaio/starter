<?php
/**
 * SPT software - Core
 * 
 * @project: https://github.com/smpleader/spt-boilerplate
 * @author: Pham Minh - smpleader
 * @description: Just class for file
 * 
 */

namespace Tests\note2_file\libraries;

use SPT\File as Base;

class File extends Base
{
    
    public function upload(array $file)
    {
        if (!$file['tmp_name'])
        {
            return false;
        }
        
        return true;
    }

}
