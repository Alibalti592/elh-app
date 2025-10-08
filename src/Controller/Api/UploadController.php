<?php

namespace App\Controller\Api;
// Tell PHP/cURL where the CA file is
putenv('SSL_CERT_FILE=C:/dev/cacert.pem');
putenv('CURL_CA_BUNDLE=C:/dev/cacert.pem');

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Kreait\Firebase\Factory;

class UploadController extends AbstractController
{
   #[Route('/upload', name: 'api_upload', methods: ['POST'])]
 public function upload(Request $request): Response
    {
        // Get the uploaded file
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        try {
            // Initialize Firebase Storage
            $storage = (new Factory())
                ->withServiceAccount(\dirname(__DIR__, 3) . '/config/firebase_credentials.json')
                ->withDefaultStorageBucket('mc-connect-5bd22') // only the bucket name
                ->createStorage();

            $bucket = $storage->getBucket();

            // Generate a unique filename
            $fileName = uniqid() . '.' . $file->guessExtension();

            // Upload the file
            $bucket->upload(
                fopen($file->getPathname(), 'r'),
                [
                    'name' => $fileName
                ]
            );
    $fileUrl = sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $fileName);
            // Return success response
            return $this->json([
                'message' => 'File uploaded successfully',
                'fileUrl' => $fileUrl
            ], 200);

        } catch (FirebaseException $e) {
            return $this->json([
                'error' => 'Firebase error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'General error: ' . $e->getMessage()
            ], 500);
        }
    }


}
