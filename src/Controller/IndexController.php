<?php
declare(strict_types = 1);

namespace AppBundle\Controller;

use \PommProject\Foundation\Pomm;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;

class IndexController
{
    private $pomm;

    public function __construct(Pomm $pomm)
    {
        $this->pomm = $pomm;
    }

    public function queryAction(Request $request): JsonResponse
    {
        $queries = $request->query->get('q');

        if ($queries === 'SHOW MEASUREMENTS LIMIT 1') {
            $response = [
                'results' => [],
            ];
        }
        elseif ($queries !== '') {
            $response = [
                'results' => [
                    [
                        'series' => [],
                    ],
                ],
            ];

            foreach (explode("\n", $queries) as $query) {
                if (empty($query)) {
                    continue;
                }

                $query = $this->transformQuery($query);
                $results = $this->pomm['default']->getQueryManager()
                    ->query($query);

                $serie = [
                    'name' => md5($query),
                    'columns' => [],
                    'values' => [],
                ];

                if (count($results) > 0) {
                    $serie['columns'] = array_keys($results->get(0));
                }

                foreach ($results as $result) {
                    $serie['values'][] = array_values($result);
                }

                $response['results'][0]['series'][] = $serie;
            }
        }
        else {
            $response = 'OK';
        }

        return new JsonResponse($response);
    }

    private function transformQuery(string $query): string
    {
        $query = preg_replace('/time ((?:<|>) now\(\))/', 'created $1', $query);
        $query = preg_replace('/time ((?:<|>) now\(\) -) (\d+\w)/', 'created $1 \'$2\'::interval', $query);
        $query = preg_replace('/time ((?:<|>) \d+)s/', 'extract(epoch from created) $1', $query);
        return $query;
    }
}
