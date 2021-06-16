<?php

namespace Kovparse;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Parser
{
    public function __construct()
    {
        $this->crawlerDetect = new CrawlerDetect;
    }

    private $output = [
        'views' => 0,
        'urls' => 0,
        'traffic' => 0,
        'total_lines' => 0,
        'crawlers' => [],
        'statusCodes' => [],
    ];
    public function getJson()
    {
        return json_encode($this->output);
    }
    public function parseLog($log)
    {
        $linesArray = explode(PHP_EOL, $log);

        $unique_urls = [];

        $data = [];
        foreach ($linesArray as $element) {
            if (!empty($element)) {
                preg_match(
                    '/^(\S*).*\[(.*)\]\s"(\S*)\s(?<url>\S*)\s([^"]*)"\s(?<code>\S*)\s(?<traffic>\S*)\s"([^"]*)"\s"(?<user>[^"]*)"$/',
                    $element,
                    $data
                );

                if (count($data) == 14) {
                    $this->addView();
                    $this->addLine();
                    if (isset($data['traffic'])) {
                        $this->addTraffic($data['traffic']);
                    }
                    if (!empty($data['url']) && !array_key_exists($data['url'], $unique_urls)) {
                        $this->addUrl();
                        $unique_urls[$data['url']] = $data['url'];
                    }
                    if (isset($data['code'])) {
                        $this->addCode($data['code']);
                    }
                    if (isset($data['user'])) {
                        $this->addCrawlers($data['user']);
                    }
                } else {
                    die('Что-то пошло не так при парсинге файла!');
                }
            }
        };
    }
    public function addView()
    {
        $this->output['views']++;
    }
    public function addLine()
    {
        $this->output['total_lines']++;
    }
    public function addTraffic($traffic)
    {
        $this->output['traffic'] += $traffic;
    }
    public function addUrl()
    {
        $this->output['urls']++;
    }
    public function addCode($code)
    {
        if (array_key_exists($code, $this->output['statusCodes'])) {
            $this->output['statusCodes'][$code]++;
        } else {
            $this->output['statusCodes'][$code] = 1;
        }
    }
    public function addCrawlers($user)
    {
        if ($this->crawlerDetect->isCrawler($user)) {
            $bot = $this->crawlerDetect->getMatches();
            if (isset($this->output['crawlers'][$bot])) {
                $this->output['crawlers'][$bot]++;
            } else {
                $this->output['crawlers'][$bot] = 1;
            }
        };
    }
}
