<?php
declare(strict_types = 1);

namespace AppBundle\Controller;

use \PommProject\Foundation\Exception\SqlException;
use \PommProject\Foundation\Pomm;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class IndexController
{
    private $databaseHost;
    private $databasePort;
    private $timeFields;

    public function __construct(
        string $databaseHost,
        string $databasePort,
        array $timeFields
    ) {
        $this->databaseHost = $databaseHost;
        $this->databasePort = $databasePort;
        $this->timeFields = $timeFields;
    }

    public function queryAction(Request $request): Response
    {
        $queries = $request->query->get('q');

        if ($queries === 'SHOW MEASUREMENTS LIMIT 1') {
            $response = [
                'results' => [],
            ];
        }
        elseif ($queries !== '') {
            $databaseName = $request->query->get('db');

            $pomm = $this->getPomm(
                $databaseName,
                $request->query->get('u'),
                $request->query->get('p')
            );

            $response = [
                'results' => [
                    [
                        'series' => [],
                    ],
                ],
            ];

            $timeField = $this->timeFields[$databaseName] ?? 'time';

            foreach (preg_split("/;|\n/", $queries) as $query) {
                if (empty($query)) {
                    continue;
                }

                $query = $this->transformQuery($query, $timeField);

                try {
                    $results = $pomm['default']->getQueryManager()
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
                catch (SqlException $e) {
                    return new Response(
                        $e->getMessage(),
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                }
            }
        }
        else {
            $response = 'OK';
        }

        return new JsonResponse($response);
    }

    private function getPomm($name, $user, $password)
    {
        $configuration = [
            'default' => [
                'dsn' => "pgsql://$user:$password@{$this->databaseHost}:{$this->databasePort}/$name",
            ],
        ];

        return new Pomm($configuration);
    }

    private function transformQuery(string $query, string $timeField): string
    {
        $query = preg_replace('/time ((?:<|>) now\(\) -) (\d+\w)/', "$timeField $1 '$2'::interval", $query);
        $query = preg_replace('/time ((?:<|>) now\(\))/', "$timeField $1", $query);
        $query = preg_replace('/time (<|>) (\d+)s/', "$timeField $1 to_timestamp($2)", $query);
        return $query;
    }
}
