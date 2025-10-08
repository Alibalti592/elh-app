<?php
namespace App\Services;

use App\Entity\Media;
use Aws\Credentials\Credentials;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class S3Service {
    CONST REGION = "eu-west-3";
    private $s3;
    private $kernelProjectDir;

    public function __construct($awsID, $awsSecret, $kernelProjectDir) {
        $credentials = new Credentials($awsID, $awsSecret);
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region'  => self::REGION,
            'credentials' => $credentials
        ]);
        $this->kernelProjectDir = $kernelProjectDir;
    }

    public function getMainBucket()
    {
        return 'muslimconect-images';
    }

    public function putObject($content, $type, $name, $bucket,  $contentType = null, $acl = 'public-read') {
        try {
            $args = [
                'Bucket' => $bucket,
                'Key'    => $name,
                'ACL'    => $acl,
            ];
            if($type == 'SourceFile') {
                $args['SourceFile'] = $content;
            } else {
                $args['Body'] = $content;
            }
            if(!is_null($contentType)) {
                $args['ContentType'] = $contentType;
            }
            $args['CacheControl'] = "max-age=25920000";
            $this->s3->putObject($args);
        } catch (Aws\S3\Exception\S3Exception $e) {
            die(dd($e));
            echo "There was an error uploading the file.\n";
        }
    }

    public function getObject($bucket, $key) {
        try {
            $result = $this->s3->getObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
            ));
            return $result['Body'];
        } catch (S3Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    public function deleteObject($bucket, $key) {
        try {
            $result = $this->s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
        } catch (S3Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    public function deleteFileFromMedia(Media $media) {
        $this->deleteObject($media->getBucket(), $media->getFolder().'/'.$media->getFilename());
    }

    /**
     * @param Media $media
     * @param $newFileName //Attention doit contenir le folder !!!!
     * @param null $newBucket
     * @param string $authorisation
     */
    public function copyMedia(Media $media, $newFileName, $newBucket = null, $authorisation = 'public-read') {
        if(is_null($newBucket)) {
            $newBucket = $media->getBucket();
        }
        $folder = '';
        if(!is_null($media->getFolder())) {
            $folder = '/'.$media->getFolder();
        }
        $source = $media->getBucket().$folder.'/'.$media->getFilename();
        try {
            $this->s3->copyObject([
                'Bucket'     => $newBucket,
                'Key'        => $newFileName,
                'CopySource' => $source,
                'ACL'        => $authorisation
            ]);
        } catch (S3Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    public function getURLFromMedia(Media $media) {
        return "https://".$media->getBucket().".s3.".self::REGION.".amazonaws.com/".$media->getFolder().'/'.$media->getFilename().'?v='.$media->getVersion();
    }

    public function getTemporaryFileLink(Media $media) {
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $media->getBucket(),
            'Key' => $media->getFolder().'/'.$media->getFilename()
        ]);
        $request = $this->s3->createPresignedRequest($cmd, '+10 minutes');
        return (string)$request->getUri();
    }

    public function getTemporaryFileLinkFromS($key, $bucket) {
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $key
        ]);
        $request = $this->s3->createPresignedRequest($cmd, '+10 minutes');
        return (string)$request->getUri();
    }

    public function getImageURL($imageName, $bucket) {
        if(is_null($imageName)) {
            return null;
        }
        return "https://".$bucket.".s3.".self::REGION.".amazonaws.com/".$imageName;
    }

    //getrandom filename
    public function randomFileName() {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < 10; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return time().$key;
    }

    /**
     * @param $bucket
     * @param $folder
     * @param $localTmpPath
     * @return string
     */
    public function saveJpegFromLocalTmpPath($bucket, $folder, $localTmpPath, $acl = 'public-read', $prefix = "") {
        $fileName = $prefix.$this->randomFileName().'.jpeg';
        $s3Path = $folder.'/'.$fileName;
        if(is_null($folder)) {
            $s3Path = $fileName;
        }
        $this->putObject($localTmpPath, 'SourceFile', $s3Path, $bucket, 'image/jpeg', $acl);
        return $fileName;
    }

    /**
     * @param $bucket
     * @param $folder
     * @param $base64Image
     * @param null $fileName with folder included
     * @return string
     */
    public function saveBase64Image($bucket, $folder, $base64Image, $fileName = null) {
        $mimeType = $this->getMimeTypeFromBase64($base64Image);
        $extension = explode('/', $mimeType )[1];
        if($extension == 'jpeg' || $extension == 'png') {
            $encodedData = preg_replace('#^data:image/[^;]+;base64,#', '', $base64Image);
            $blob = base64_decode($encodedData);
            if(is_null($fileName)) {
                $fileName = $this->randomFileName().'.'.$extension;
            } else { //replace extension in case of switch from png to jpeg
                $fileName = explode('.', $fileName)[0].'.'.$extension;
            }
            $filePath = $folder.'/'.$fileName;
            $this->putObject($blob, 'Body', $filePath, $bucket, $mimeType);
            return $fileName;
        }
        return null;
    }

    public function saveBase64File($bucket, $folder, $base64PDF, $fileName, $acl = 'private') {
        $mimeType = $this->getMimeTypeFromBase64($base64PDF);
        $encodedData = preg_replace('#^data:'.$mimeType.';base64,#', '', $base64PDF);
        $blob = base64_decode($encodedData);
        $fileName = $this->randomFileName().$fileName;
        $filePath = $folder.'/'.$fileName;
        $this->putObject($blob, 'Body', $filePath, $bucket, $mimeType, $acl);
        return $fileName;
    }

    public function saveBase64PDF($bucket, $folder, $base64PDF, $acl = 'private') {
        $mimeType = $this->getMimeTypeFromBase64($base64PDF);
        $encodedData = preg_replace('#^data:application/pdf;base64,#', '', $base64PDF);
        $blob = base64_decode($encodedData);
        $fileName = $this->randomFileName().'.pdf';
        $filePath = $folder.'/'.$fileName;
        $this->putObject($blob, 'Body', $filePath, $bucket, $mimeType, $acl);
        return $fileName;
    }


    public function getMimeTypeFromBase64($base64Image) {
        $pos  = strpos($base64Image, ';');
        return explode(':', substr($base64Image, 0, $pos))[1];
    }

    /**
     * On resize sur la largeur, crop si necessaire en hauteur
     * @param $image (bytes)
     * @param $maxWidth
     * @param $maxHeight
     * @param false $crop
     * @return false|string
     */
    public function optimizeImageBeforeUpload($originalImage, $maxWidth, $maxHeight, $crop) {
        $original_w = imagesX($originalImage);
        $original_h = imagesY($originalImage);
        $scaled_w = $original_w;
        $scaled_h = $original_h;
        if (!$crop && ($original_w > $maxWidth || $original_h > $maxHeight)) {
            if ($original_w >= $original_h) {
                $scaled_w = $maxWidth;
                $scale = $maxWidth / $original_w;
                $scaled_h = ceil($original_h * $scale);
            } else {
                $scaled_h = $maxHeight;
                $scale = $maxHeight / $original_h;
                $scaled_w = ceil($original_w * $scale);
            }
        } elseif($crop && $original_w > $maxWidth) { //scale only on width if needed
            $scaled_w = $maxWidth;
            $scale = $maxWidth / $original_w;
            $scaled_h = ceil($original_h * $scale);
        }
        $scaled = imageCreateTrueColor($scaled_w, $scaled_h);
        imageCopyResampled($scaled, $originalImage, 0, 0, 0, 0, $scaled_w, $scaled_h, $original_w, $original_h);
        $fullPath = $this->getTmpImagePath();
        if($crop && $scaled_h > $maxHeight) {
            $this->crop($scaled_w, $scaled_h, $maxWidth, $maxHeight, $scaled, $fullPath);
        } else {
            imageJpeg($scaled, $fullPath, 100);
        }
        imagedestroy($scaled);
        imagedestroy($originalImage);
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($fullPath);
        return $fullPath;
    }

    public function image_fix_orientation(&$image, $filename) {
        try {
            $exif = exif_read_data($filename);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;

                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;

                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                }
            }
        } catch (\Throwable $t) {

        }
    }

    public function getTmpImagePath($extension = 'jpeg') {
        $tmpFileName =  $this->randomFileName().'_tmp.'.$extension;
        $directory = $this->kernelProjectDir.'/var/tmp';
        $fullPath = $directory.'/'.$tmpFileName;
        if(!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        return $fullPath;
    }

    public function getImageFromBase64($baseImage) {
        $extension = explode('/', $this->getMimeTypeFromBase64($baseImage) )[1];
        if(!in_array($extension, ['jpeg', 'png', 'gif'])) {
            return false;
        }
        $tempFullPath = $this->getTmpImagePath();
        if($extension == 'png') {
//            $original = imagecreatefrompng($baseImage); //pbs ici alors que le base64 semble OK !!!
            $original = $this->setPngFromBase64($baseImage);
            $original_w = imagesX($original);
            $original_h = imagesY($original);
            $bg = imagecreatetruecolor($original_w, $original_h);
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, TRUE);
            imagecopy($bg, $original, 0, 0, 0, 0, $original_w, $original_h);
            imageJpeg($bg, $tempFullPath, 100);
            imagedestroy($bg);
            $original = imagecreatefromjpeg($tempFullPath);
            unlink($tempFullPath);
        } elseif($extension == 'gif') {
            $original = imagecreatefromgif($baseImage);
        } else {
            $original = imagecreatefromjpeg($baseImage);
        }
        return $original;
    }

    public function setPngFromBase64($base64Image) {
        $img = str_replace('data:image/png;base64,', '', $base64Image);
        $data = base64_decode($img);
        return imagecreatefromstring($data);
    }

    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight, $image, $fullPath) {
        $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
        $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
        $cropImage = imagecreatetruecolor($newWidth , $newHeight);
        imagecopyresampled($cropImage, $image , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
        imageJpeg($cropImage, $fullPath, 100);
        imagedestroy($cropImage);
    }

    public function isImage($mimeType)
    {
        return in_array($mimeType, [
            'image/jpeg',  // JPEG images
            'image/png',   // PNG images
            'image/gif',   // GIF images
            'image/webp',  // WebP images
            'image/bmp',   // BMP images
            'image/svg+xml', // SVG images
            'image/tiff'   // TIFF images
        ]);
    }
    
    public function getBase64ImageFromTmpPath($tmpPath) {
        $fileContent = file_get_contents($tmpPath);
        $base64String = base64_encode($fileContent);
        $mimeType = mime_content_type($tmpPath);
        unlink($tmpPath);
        return 'data:' . $mimeType . ';base64,' . $base64String;
    }

    public function getBase64FileSizeInMB($base64String) {
        // Split the base64 string to remove the metadata (if present)
        $base64String = preg_replace('/^data:\w+\/\w+;base64,/', '', $base64String);
        // Decode the base64 string
        $binaryData = base64_decode($base64String);
        // Calculate the size of the binary data in bytes
        $fileSize = strlen($binaryData);

        return round(($fileSize/ 1024)/1024, 3);
    }

}