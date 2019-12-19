<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Nesk\Puphpeteer\Puppeteer;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q', null);
        if ($q === null) {
            return response()->json(['error']);
        }

        $puppeteer = new Puppeteer();
        $browser = $puppeteer->launch([
            'args' => ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=300,400'],
        ]);

        $page = $browser->newPage();
        $page->goto("https://www.google.com/search?q={$q}&num=10&start=0&hl=zh-CN");
        $html = $page->content();

        $browser->close();

        $crawler = new Crawler();
        $crawler->addHtmlContent($html);

        $count = null;
        $searchResultCount = $crawler->filterXPath("//div[@id='resultStats']")->text();
        if(preg_match('/找到约 (.*?) 条结果/', $searchResultCount, $match)){
            $count = str_replace(',', '' , $match[1]);
        }

        $result = $crawler->filterXPath("//div[@id='search']//div[@class='g']")->each(function (Crawler $node, $i) {
            if ($node->filterXPath('//h3')->count() === 0 || $node->filterXPath('//cite')->count() === 0
                || $node->filterXPath('//span[@class="st"]')->count() === 0) {
                return null;
            }

            return [
                'title' => $node->filterXPath('//h3')->text(),
                'url' => $node->filterXPath('//cite')->text(),
                'intro' => $node->filterXPath('//span[@class="st"]')->html(),
            ];
        });

        return response()->json([
            'data' => array_filter($result),
            'count' => $count
        ]);
    }
}
