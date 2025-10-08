<?php
namespace App\Command;

use App\Entity\Location;
use App\Entity\Mosque;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

//remise à 0 pour envoi le lendemain
// => éxecuter la nuit 1 seule fois et éviter que l'autre tache d'envoi puisse s'éxecuter en même temps !!!
class ImportMosque extends Command
{
    protected static $defaultName = 'app:import-mosque';

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        set_time_limit ( 10000 );
        $csvFilePath = '/var/www/mosques.csv';
        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(0); // Première ligne utilisée comme en-tête
        foreach ($csv as $record) {
            $address = $record['Adresse'].','.$record['Code Postal'].','.$record['Ville'].',France';
            $coord = $this->getCoordinates($address);
            if(!is_null($coord)) {
                $location = new Location();
                $location->setAdress($record['Adresse']);
                $location->setCity($record['Ville']);
                $location->setPostCode($record['Code Postal']);
                $location->setLabel($record['Ville']);
                $location->setLat($coord['latitude']);
                $location->setLng($coord['longitude']);
                $location->setRegion($record['region']);
                $mosque = new Mosque();
                $mosque->setName($record['Nom']);
                $mosque->setTel($record['Tel']);
                $mosque->setLocation($location);
                $this->entityManager->persist($location);
                $this->entityManager->persist($mosque);
                $this->entityManager->flush();
            }
           sleep(0.5);
        }
        return Command::SUCCESS;
    }

    function getCoordinates($address) {
        $address = urlencode($address);
        $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0'); // Set User-Agent

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!empty($data)) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon']
            ];
        } else {
            return null;
        }
    }
}