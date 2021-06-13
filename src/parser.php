<?php

namespace Kparse\src;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Parser
{
    public function parse($log)
    {
        $linesArray = explode(PHP_EOL, $log);

        $output = [
            'views' => 0,
            'urls' => 0,
            'traffic' => 0,
            'total_lines' => 0,
            'crawlers' => [],
            'statusCodes' => [],
        ];
        $unique_urls = [];
        $data = [];

        $crawlerDetect = new CrawlerDetect;
        foreach ($linesArray as $element) {
            preg_match(
                '/^(\S*).*\[(.*)\]\s"(\S*)\s(?<url>\S*)\s([^"]*)"\s(?<code>\S*)\s(?<traffic>\S*)\s"([^"]*)"\s"(?<user>[^"]*)"$/',
                $element,
                $data
            );

            if (!empty($data)) {
                $output['views']++;
                $output['total_lines']++;
                if (isset($data['traffic'])) {
                    $output['traffic'] += $data['traffic'];
                }
            }
            if (!empty($data['url']) && !array_key_exists($data['url'], $unique_urls)) {
                $output['urls']++;
                $unique_urls[$data['url']] = $data['url'];
            }
            if (isset($data['code'])) {
                if (isset($output['statusCodes'][$data['code']])) {
                    $output['statusCodes'][$data['code']]++;
                } else {
                    $output['statusCodes'][$data['code']] = 1;
                }
            }
            if (isset($data['user'])) {
                if ($crawlerDetect->isCrawler($data['user'])) {
                    $bot = $crawlerDetect->getMatches();

                    if (isset($output['crawlers'][$bot])) {
                        $output['crawlers'][$bot]++;
                    } else {
                        $output['crawlers'][$bot] = 1;
                    }
                };
            }
        };
        return $output;
    }
}
