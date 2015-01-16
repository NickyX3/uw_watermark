<?php
/**
 * CKFinder Plug-In for add watermarks via exec ImageMagick Composite binary
 */
class UWWatermark {
	// where composite binary
	private	$composite_binary	= '/usr/bin/env composite';
	
	/**
	 * Public Event Method, like original CKFinder Plug-In
	 */
	public function onAfterFileUpload ( $currentFolder, $uploadedFile, $sFilePath ) {
        global $config;
        
        $watermarkSettings = $config['Plugin_UWWatermark'];
        
        $this->createWatermark($sFilePath,$watermarkSettings['source']);

        return true;
    }
    
    /**
     * WaterMarks Creations Method
     */
    private function createWatermark ( $sourceFile='', $watermarkFile='logo.png' ) {
    	// add watermarks if only add checkbox and set in upload form ( see addcheckbox.js )
    	if ( isset($_POST['watermark']) && $_POST['watermark'] == 'on' ) {
    		$addwatermark = true;
    	} else {
    		$addwatermark = false;
    	}
    	// get watermark file from location this plug-in
    	$watermarkfile = dirname(__FILE__).'/'.$watermarkFile;
    	
    	// check all needs, need add watermark, exist picture, exist watermark picture
    	if ( $addwatermark === true && $sourceFile != '' && file_exists($sourceFile) && file_exists($watermarkfile) ) {
    		// prefix for change original file name, if empty - file with watermarks replace original
    		$prefix		= '';
    		// explode path to original file for change name with some $prefix
    		$pathpieces	= pathinfo($sourceFile);
    		$new_name 	= $pathpieces['dirname'].'/'.$prefix.$pathpieces['filename'].'.'.$pathpieces['extension'];
    		
    		// get images sizes
    		$imagesize 	= @getimagesize($sourceFile);
    		$wmsize 	= @getimagesize($watermarkfile);

    		// picture size
    		$width 	= $imagesize[0];
    		$height = $imagesize[1];
    		
    		// watermark size
    		$w		= $wmsize[0];
    		$h		= $wmsize[1];
    			
    		// first picture, in top 1/3, some random
    		$x1 	= intval( rand( $w/2 + 20, ( $width - $w/2 ) - 20 ) );
    		$y1		= intval( rand( $h/2 + 20, ( $height - 20 ) / 3 ) );
    			
    		// second picture, in bottom 1/3, some random
    		$x2 	= intval( rand( $w/2 + 20, ( $width - $w/2 ) - 20 ) );
    		$y2		= intval( rand( ( ( $height - 20 ) * 2 / 3 ) + $h/2, $height - $h/2 - 20 ) );
    			
    		// center picture
    		$x 		= $width/2 	- $w/2;
    		$y 		= $height/2 + $h/2;
    		
    		// if width picture lower twice watermark - set only one center watermark
    		if ( $width <= $w * 2 ) {
    			$commands[] = $this->composite_binary.' -compose over -quality 93  -geometry +'.$x.'+'.$y.' '.$watermarkfile.' '.$sourceFile.' '.$new_name;
    		} else {
    			// 3 watermarks
    			$commands[] = $this->composite_binary.' -compose over -quality 100 -geometry +'.$x1.'+'.$y1.' '.$watermarkfile.' '.$sourceFile.' '.$new_name;
    			$commands[] = $this->composite_binary.' -compose over -quality 100 -geometry +'.$x2.'+'.$y2.' '.$watermarkfile.' '.$new_name.' '.$new_name;
    			$commands[] = $this->composite_binary.' -compose over -quality 93  -geometry +'.$x.'+'.$y.' '.$watermarkfile.' '.$new_name.' '.$new_name;
    		}
    		// init exit codes pool
    		$exitcode = 0;
    		// exexc commands
    		foreach ( $commands as $comm ) {
    			exec ($comm,$out,$exitcode);
    		}
    		// check exitcodes, errors is > 0
    		if ( $exitcode == 0 ) {
    			return $new_name;
    		} else {
    			return false;
    		}
    	} else {
    		return false;
    	}
    	
    }
}

$watermark = new UWWatermark();
$config['Hooks']['AfterFileUpload'][] = array($watermark, 'onAfterFileUpload');
if (empty($config['Plugin_UWWatermark']))
{
	$config['Plugin_UWWatermark'] = array(
			"source" => "logo.png",
	);
}
