<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\storage\fs;

class ImageBuilder extends core\module\Filed {

  public function __construct(fs\file $file) {

    $this->setFile($file);
  }

  protected function resize($sExtension, $iMaxWidth, $iMaxHeight, $bCrop = false) {

    $aExtensions = array('jpeg', 'png', 'gif');

    if (!in_array($sExtension, $aExtensions)) {

      $this->launchException('Cannot edit image, unknown extension');
    }

    // Calcul des nouvelles dimensions

    $file = $this->getFile();
    list($iWidth, $iHeight) = getimagesize($file->getRealPath());

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

    $preview = imagecreatetruecolor($iPreviewWidth, $iPreviewHeight);

    if ($sExtension == 'png' || $sExtension == 'gif') {

      imagealphablending($preview, false);
      $iTransparent = imagecolortransparent($preview, imagecolorallocatealpha($preview, 0, 0, 0, 127));
      imagefill($preview, 0, 0, $iTransparent);
      imagesavealpha($preview, true);
    }

    $sFunction = 'imagecreatefrom'.$sExtension;
    $source = @$sFunction($file->getRealPath()) or die("Cannot Initialize new GD image stream");

    // Redimensionnement

    imagecopyresampled($preview, $source, 0, 0, $iXSource, $iYSource, $iPreviewWidth, $iPreviewHeight, $iSourceWidth, $iSourceHeight);

    return $preview;
  }

  public function build(fs\editable\file $file, $iWidth, $iHeight, $sFilter = '') {

    $sExtension = strtolower($file->getExtension());
    if ($sExtension == 'jpg') $sExtension = 'jpeg';

    $img = $this->resize($sExtension, $iWidth, $iHeight, true);

    if ($sFilter) {

      $this->filter($img, $sFilter);
    }

    $sFunction = 'image'.$sExtension;

    $sFunction($img, $file->getRealPath());
    imagedestroy($img);
  }

  protected function filter($img, $sFilter) {

    //imagestring($img, 2, 5, 15, date('H:i:s'), imagecolorallocate($img, 255, 216, 147));
    
    switch ($sFilter) {

      case 'grayscale' :

        imagefilter($img, IMG_FILTER_GRAYSCALE);
        break;

      default :

        $this->launchException("Unknown filter : '$sFilter'");
    }
  }
}
