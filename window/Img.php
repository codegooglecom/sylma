<?php

class WindowImg implements WindowInterface {
  
  private $oFile = null;
  
  public function loadAction($oFile) {
    
    if ($oFile instanceof XML_File) $this->oFile = $oFile;
  }
  
  public function resize($sExtension, $iMaxWidth, $iMaxHeight, $bCrop = false) {
    
    // Calcul des nouvelles dimensions
    
    list($iWidth, $iHeight) = getimagesize(MAIN_DIRECTORY.$this->oFile);
    
    $iWidthRatio = $iHeightRatio = 1;
    $iXSource = $iYSource = 0;
    
    $iSourceHeight = $iHeight;
    $iSourceWidth = $iWidth;
    
    // look up for ratios
    
    if ($iWidth > $iMaxWidth) {
      
      $iWidthRatio = $iWidth / $iMaxWidth;
      $iPreviewWidth = $iMaxWidth;
      
    } else $iPreviewWidth = $iWidth;
    
    if ($iHeight > $iMaxHeight) {
      
      $iHeightRatio = $iHeight / $iMaxHeight;
      $iPreviewHeight = $iMaxHeight;
      
    } else $iPreviewHeight = $iHeight;
    
    // set croping
    if ($iWidthRatio > $iHeightRatio) {
      
      if ($bCrop) {
        
        $iSourceWidth = $iPreviewWidth * $iHeightRatio;
        $iXSource = ($iWidth - $iSourceWidth) / 2;
        
      } else $iPreviewWidth = $iSourceWidth;
      
    } else if ($iWidthRatio < $iHeightRatio) {
      
      if ($bCrop) {
        
        $iSourceHeight = $iPreviewHeight * $iWidthRatio;
        $iYSource = ($iHeight - $iSourceHeight) / 2;
        
      } else $iPreviewHeight = $iSourceHeight;
      
    }
    
    $oImagePreview = imagecreatetruecolor($iPreviewWidth, $iPreviewHeight);
    
    if ($sExtension == 'png' || $sExtension == 'gif') {
      
      imagealphablending($oImagePreview, false);
      $iTransparent = imagecolortransparent($oImagePreview, imagecolorallocatealpha($oImagePreview, 0, 0, 0, 127));
      imagefill($oImagePreview, 0, 0, $iTransparent);
      imagesavealpha($oImagePreview, true);
    }
    
    $sExtension = strtolower($this->oFile->getExtension());
    if ($sExtension == 'jpg') $sExtension = 'jpeg';
    
    $sFunction = 'imagecreatefrom'.$sExtension;
    $oImageSource = @$sFunction(MAIN_DIRECTORY.$this->oFile) or die("Cannot Initialize new GD image stream");
    
    // Redimensionnement
    
    imagecopyresampled($oImagePreview, $oImageSource, 0, 0, $iXSource, $iYSource, $iPreviewWidth, $iPreviewHeight, $iSourceWidth, $iSourceHeight);
    
    return $oImagePreview;
  }
  
  public function __toString() {
    
    if ($this->oFile) {
      
      $sFilePath = (string) $this->oFile;
      
      $sExtension = strtolower($this->oFile->getExtension());
      if ($sExtension == 'jpg') $sExtension = 'jpeg';
      
      $aExtensions = array('jpeg', 'png', 'gif');
      
      $iWidth = Controler::getPath()->getAssoc('width');
      $iHeight = Controler::getPath()->getAssoc('height');
      
      if (!$iWidth && !$iHeight) {
        
        Controler::setContentType($sExtension);
        
        return $this->oFile->read();
        
      } else if (in_array($sExtension, $aExtensions)) {
        
        Controler::setContentType($sExtension);
        
        if (!$iWidth) $iWidth = $iHeight;
        if (!$iHeight) $iHeight = $iWidth;
        
        $img = $this->resize($sExtension, $iWidth, $iHeight, true);
        
        // imagefilter($img, IMG_FILTER_GRAYSCALE);
        // imagestring($img, 2, 5, 15, date('H:i:s'), imagecolorallocate($img, 255, 216, 147));
        
        $sFunction = 'image'.$sExtension;
        
        $sFunction($img);
        imagedestroy($img);
        
        exit;
      }
      
    } else Controler::error404();
  }
}

