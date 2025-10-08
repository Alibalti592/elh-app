<?php
namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGeneratorService {

    public function __construct(private readonly S3Service $s3Service){}

    public function generatePdf(string $htmlContent, string $fileName) {
        // Configure DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        // Get PDF content
        $pdfContent = $dompdf->output();
        // Upload to S3
        $filePath = 'testament/' . $fileName;
        $bucket = "muslimconnect-private";
        $this->s3Service->putObject($pdfContent, 'Body', $filePath, $bucket, 'application/pdf', 'private' ); //s3 URL
        return $this->s3Service->getTemporaryFileLinkFromS($filePath, $bucket);
    }
}