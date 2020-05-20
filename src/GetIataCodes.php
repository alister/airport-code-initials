<?php
namespace App;

use App\Web\IataCsv;
use Generator;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Serializer\SerializerInterface;

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
        $resource = fopen($tempnam, 'w+b');
        $this->guzzle->request('GET', self::SRC_URI, ['sink' => $resource]);

        return $tempnam;
    }

    public function writeSummaryToCsv(string $filename, string $destfile): void
    {
        $arr = [];
        foreach ($this->summariseIataList($filename) as $data) {
            $arr[] = $data;
        }
        // @todo sort by ['iata_code'] comparison

        file_put_contents($destfile, $this->serializer->encode($arr, 'csv'));
    }

    public function writeSummaryToJsModuleArray(string $filename, string $destfile): void
    {
        foreach ($this->summariseIataList($filename) as $data) {
            $arr[$data['iata_code']] = $data['location'];
        }
        ksort($arr);

        ob_start();
        echo 'module.exports = ', json_encode($arr, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_OBJECT_AS_ARRAY),
            ';',
            PHP_EOL;

        file_put_contents($destfile, ob_get_clean());
    }

    public function summariseIataList(string $filename): Generator
    {
        $data = $this->serializer->decode(file_get_contents($filename), 'csv');

        $this->count = 0;

        foreach ($data as $item) {
            if (!$this->useIataCode($item)) {
                continue;
            }

            try {
                $country = Countries::getName($item['iso_country']);
            } catch (MissingResourceException $exception) {
                // country code invalid, not known, or at least not in the package (yet?) - ignore
                continue;
            }

            $location = array_filter([$item['name'], $item['municipality'], $country]);

            $this->count ++;
            yield [
                'iata_code' => $item['iata_code'],
                'location' => implode(', ', $location)
            ];
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
