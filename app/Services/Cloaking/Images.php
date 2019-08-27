<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class Images
{
    public const LEFT_TOP = 0;
    public const LEFT_BOTTOM = 1;
    public const RIGHT_TOP = 2;
    public const RIGHT_BOTTOM = 3;

    public const REFLECTION_NONE = 0;
    public const REFLECTION_HORIZONTAL = 1;
    public const REFLECTION_VERTICAL = 2;
    public const REFLECTION_DOUBLE = 3;

    private $_imageSize = array();

    public function handleIfImage(&$file, $mime, Array $data = null)
    {
        $isImage = (Images::isImage($mime));
        $GDIsActive = (function_exists('imagecreatefromjpeg'));

        if (!$isImage OR !$GDIsActive OR strpos($file, ';base64')) {
            return $file;
        } else {
            $this->_handleImage($file, $data);
        }
    }

    public static function isImage($mimeType)
    {
        return (in_array($mimeType, Constants::$IMAGES_TYPES));
    }

    private function _handleImage(&$file, Array $data = null)
    {
        $file = imagecreatefromstring($file);

        $this->_imageSize = $this->_getImageSize($file);

        $width = (int)ParserSettings::get('img_min_w');
        $height = (int)ParserSettings::get('img_min_h');
        $width = ($width) ? $width : 150;
        $height = ($height) ? $height : 150;

        $notWidth = ($this->_imageSize['width'] < $width);
        $notHeight = ($this->_imageSize['height'] < $height);

        if(!headers_sent()) header('Content-type: image/jpeg', true);
        if ($notHeight OR $notWidth) {
            return @imagejpeg($file);
        }

        $this->_reflectionImageIfNeed($file, $data);
        $this->_addLogoIfNeed($file, $data);

        return imagejpeg($file);
    }

    private function _getImageSize($image)
    {
        return array('width' => @imagesx($image),
            'height' => imagesy($image));
    }

    public function _reflectionImageIfNeed(&$file, Array $data = null)
    {
        $reflectionType = (isset($data['reflection'])) ? $data['reflection'] : ParserSettings::get('reflection');
        if ($reflectionType) {
            $this->_reflectionImage($file, (int)$reflectionType);
        }
    }

    public function _reflectionImage(&$file, $reflectionType)
    {
        $size = $this->_imageSize;

        $reflected = imagecreatetruecolor($size['width'], $size['height']);

        imagealphablending($reflected, false);
        imagesavealpha($reflected, true);

        for ($y = 1; $y <= $size['height']; ++$y) {
            for ($x = 0; $x < $size['width']; ++$x) {
                $width = $size['width'] - ($x + 1);
                $height = $size['height'] - $y;
                switch ($reflectionType) {
                    case self::REFLECTION_HORIZONTAL:
                        $newX = $width;
                        $newY = $y - 1;
                        break;
                    case self::REFLECTION_VERTICAL:
                        $newY = $height;
                        $newX = $x;
                        break;
                    case self::REFLECTION_DOUBLE:
                        $newX = $width;
                        $newY = $height;
                        break;
                }
                $rgba = imagecolorat($file, $newX, $newY);
                $rgba = imagecolorsforindex($file, $rgba);
                $rgba = imagecolorallocatealpha($reflected, $rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);
                imagesetpixel($reflected, $x, $y - 1, $rgba);
            }
        }
        $file = $reflected;
    }

    private function _addLogoIfNeed(&$file, Array $data = null)
    {
        $enable = (isset($data['enable_copyright'])) ? $data['enable_copyright'] : ParserSettings::get('enable_copyright');

        if ($enable) {

            $logo = (isset($data['logo'])) ? $data['logo'] : ParserSettings::get('logo');
            if (isset($logo)) {
                $logoPos = (isset($data['logoPos'])) ? $data['logoPos'] : (int)ParserSettings::get('logoPos');
                $this->_addLogoToImage($file, $logo, $logoPos, $data);
            }
        }
    }

    private function _addLogoToImage(&$file, $logoPath, $logoPos, Array $data = null)
    {
        $imageSize = $this->_getImageSize($file);

        $needSetFonCollor = (isset($data['logoFont'])) ? $data['logoFont'] : ParserSettings::get('logoFont');
        $isTextImage = (strpos($logoPath, 'text::') === 0);

        if ($isTextImage and !$needSetFonCollor) {

            $str = substr($logoPath, 6);
            $logoSize = array('height' => 10,
                'width' => strlen($str) * 10);

            $font = (int)$imageSize['width'] / Constants::LOGO_FONT_DELIMITER;

            $logoPos = $this->_getLogoPos($imageSize,
                $logoSize,
                $logoPos,
                $font);
            $color = (isset($data['logo_collor'])) ? $data['logo_collor'] : ParserSettings::get('logo_collor');

            @imagettftext($file,
                $font,
                0,
                $logoPos['x'],
                $logoPos['y'],
                str_replace('#', '0x', $color),
                './dolly_templates/fonts/arial.ttf',
                $str);

        } else {
            $font = (int)$imageSize['width'] / 50;

            $logo = file_get_contents($logoPath);
            $logo = @imagecreatefromstring($logo);

            $logoSize = $this->_getImageSize($logo);
            $logoPos = $this->_getLogoPos($imageSize,
                $logoSize,
                $logoPos,
                $font);

            imagealphablending($file, true);
            imagealphablending($logo, true);

            imagecopy($file,
                $logo,
                $logoPos['x'],
                $logoPos['y'],
                0,
                0,
                $logoSize['width'],
                $logoSize['height']);

        }
    }

    private function _getLogoPos($imageSize, $logoSize, $logoPos, $font)
    {
        switch ($logoPos) {
            case self::LEFT_TOP:
                $x = Constants::LOGO_PADDING + $font + 10;
                $y = Constants::LOGO_PADDING + ($font * 2);
                break;
            case self::LEFT_BOTTOM:
                $x = Constants::LOGO_PADDING + $font + 10;
                $y = $imageSize['height'] - $logoSize['height'] - Constants::LOGO_PADDING - ($font * 2);
                break;
            case self::RIGHT_TOP:
                $x = $imageSize['width'] - $logoSize['width'] - Constants::LOGO_PADDING - $font - 10;
                $y = Constants::LOGO_PADDING + ($font * 2);
                break;
            case self::RIGHT_BOTTOM:
                $x = $imageSize['width'] - $logoSize['width'] - Constants::LOGO_PADDING - $font - 10;
                $y = $imageSize['height'] - $logoSize['height'] - Constants::LOGO_PADDING - ($font * 2);
        }
        return array('x' => $x,
            'y' => $y);
    }


}
