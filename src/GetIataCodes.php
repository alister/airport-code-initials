<?php
namespace App;

use App\Web\IataCsv;
use Generator;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Intl\Intl;

class GetIataCodes
{
    private const SRC_URI = 'https://raw.githubusercontent.com/datasets/airport-codes/master/data/airport-codes.csv';

    /** @var IataCsv */
    private $guzzle;
    /** @var SerializerInterface */
    private $serializer;

    public $count = 0;
    
    public function __construct(IataCsv $guzzle, SerializerInterface $serializer) 
    {
        $this->guzzle = $guzzle;
        $this->serializer = $serializer;
    }

    public function downloadLatestIataCodesList(): string
    {
        $tempnam = tempnam(sys_get_temp_dir(), 'iata_codes_csv');
        $resource = fopen($tempnam, 'w+');
        $this->guzzle->request('GET', self::SRC_URI, ['sink' => $resource]);
        
        return $tempnam;
    }

    public function writeSummary(string $filename, string $destfile = '../data.csv')
    {
        foreach ($this->summariseCsv($filename) as $data) {
            $csvData[] = $data;
        }

        usort($csvData, function (array $a, array $b) {
            return ($a['iata_code'] <=> $b['iata_code']); 
        });
        
        file_put_contents($destfile, $this->serializer->encode($csvData, 'csv'));
    }
    
    public function summariseCsv(string $filename): Generator
    {
        $data = $this->serializer->decode(file_get_contents($filename), 'csv');
       
        $this->count = 0;

        foreach ($data as $item) {
            if (!$this->useIataCode($item)) {
                continue;
            }

            $country = Intl::getRegionBundle()->getCountryName($item['iso_country']);
            
            yield [
                'iata_code' => $item['iata_code'],
                'location' => $item['name']. ', '. $item['municipality'].', '. $country
            ];

            $this->count ++;
        }
    }

    private function useIataCode(array $data): bool 
    {
        $iataCode = $data['iata_code'];
        $isClosed = $data['type'] === 'closed';
        $isThreeAlphas = strlen($iataCode) === 3 && ctype_alpha($iataCode);

        return (!$isClosed && $isThreeAlphas);
    }
}
