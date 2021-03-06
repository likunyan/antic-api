<?php

namespace App\Console\Commands;

use App\Models\WeiboHot;
use App\Models\WeiboToTop;
use Illuminate\Console\Command;

class WeiboHotSpider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weibo:hot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬微博热搜榜';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $html = file_get_contents('https://s.weibo.com/top/summary');
        $htmlNoBlank = preg_replace("/>\n\s*/i", '>', $html);
        preg_match('/td-02.*?="(.*?)".*?>(.*?)<\/a>.*?<i.*?>(.*?)<\/i>/si', $htmlNoBlank, $topping);
        $deleteTopping = preg_replace('/<tbody.*?<\/tr>/i', '', $htmlNoBlank);
        $pattern = "/td-02.*?><a.*?href=\"(.*?)\".*?>(.*?)<\/a><span>(.*?)<\/span>.*?td-03.*?>(.*?)<\/td>/i";
        preg_match_all($pattern, $deleteTopping, $matches, PREG_SET_ORDER);

        // 置顶条目
        $toppingData = [
            'title' => $topping[2],
            'url' => $topping[1],
            'status' => status($topping[3]),
        ];

        if (! WeiboToTop::where('title', $toppingData['title'])->exists()) {
            try {
                WeiboToTop::create($toppingData);
            } catch (\Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1];
                echo $errorCode;
            }
        }

        // 前50
        foreach ($matches as $i) {
            $url = $i[1];
            if ($url === 'javascript:void(0);') {
                continue;
            }
            $rank = $i[3];
            $title = $i[2];
            $status = $i[4] ?? null;

            if (preg_match('/<img.*?>/i', $i[0], $emojiMatches)) {
                $emoji = $emojiMatches[0];
            } else {
                $emoji = null;
            }

            if ($status && preg_match("/<i.*?>(.*?)<\/i>/i", $i[4], $statusMatches)) {
                $status = $statusMatches[1];
            }

            $updateData = [
                'rank' => $rank,
                'emoji' => $emoji,
                'status' => status($status),
                'url' => $url,
            ];

            WeiboHot::updateOrCreate(['title' => $title], $updateData);
        }
    }
}
