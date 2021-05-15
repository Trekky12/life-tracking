<?php

namespace App\Domain\User\Profile;

use RobThree\Auth\Providers\Qr\IQRCodeProvider;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\Writer\PngWriter;

class QRCodeProvider implements IQRCodeProvider {

    public function getMimeType() {
        return 'image/png';
    }

    public function getQRCodeImage($qrtext, $size) {
        $qrCode = new QrCode($qrtext);
        $qrCode->setSize($size);
        $qrCode->setMargin(0);
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevelMedium());

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return $result->getString();
    }

}
