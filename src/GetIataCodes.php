<?php
namespace App;

use App\Web\IataCsv;
use Symfony\Component\Serializer\SerializerInterface;

class GetIataCodes
{
    private const SRC_URI = 'https://raw.githubusercontent.com/datasets/airport-codes/master/data/airport-codes.csv';

    /** @var IataCsv */
    private $guzzle;
    /** @var SerializerInterface */
    private $serializer;

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

    public function summariseCsv(string $filename)
    {
        $data = $this->serializer->decode(file_get_contents($filename), 'csv');
        dump($data[0], $data[1],$data[2]);
    }
}
