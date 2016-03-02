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

    public function __construct($databaseHost, $databasePort)
    {
        $this->databaseHost = $databaseHost;
        $this->databasePort = $databasePort;
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
            $pomm = $this->getPomm(
                $request->query->get('db'),
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

            foreach (explode("\n", $queries) as $query) {
                if (empty($query)) {
                    continue;
                }

                $query = $this->transformQuery($query);

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

    private function transformQuery(string $query): string
    {
        $query = preg_replace('/time ((?:<|>) now\(\))/', 'created $1', $query);
        $query = preg_replace('/time ((?:<|>) now\(\) -) (\d+\w)/', 'created $1 \'$2\'::interval', $query);
        $query = preg_replace('/time ((?:<|>) \d+)s/', 'extract(epoch from created) $1', $query);
        return $query;
    }
}
