<?php

namespace App\Console\Commands;

use Facade\Ignition\Support\FakeComposer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $infoId = 10000;

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
     * @return int
     */
    public function handle()
    {
        foreach(range(1, 20000) as $i) {
            $sql = '';
            foreach(range(1, 100) as $j) {
                $sql .= $this->genSql();
            }
            // DB::unprepared($sql);
            $this->info($i);
            file_put_contents(storage_path('batch_user2.sql'), $sql . "\n", FILE_APPEND);
        }
    }

    protected function genSql()
    {
        $faker = \Faker\Factory::create('zh_CN');
        $this->infoId++;
        $infoId = $this->infoId;


        $name = $faker->name();
        $externalUserID = $faker->uuid();
        $unionid = md5($infoId);
        $nowTime = date('Y-m-d H:i:s');
        

        $sql1 = "INSERT INTO `t_externaluserinfo` (            `ID`,            `BrandID`,            `ExternalUserID`,            `Name`,            `Position`,            `Avatar`,            `Mobile`,            `CorpName`,            `CorpFullName`,            `ContactType`,            `Gender`,            `UnionID`,            `ExternalProfile`,            `IsMember`,            `CreateTime`,            `UpdateTime`,            `DeleteTime`,            `member_id`         )        VALUES            (                {$infoId},                1,                '{$externalUserID}',                '{$name}',                NULL,                'http://wx.qlogo.cn/mmhead/fhicotyX5dAcaCBKJuA0GjWNXLD4MGk2AGpmzMgwuP0lG77ymiazgxWw/0',                NULL,                NULL,                NULL,                1,                2,                '{$unionid}',                'null',                 '1',                '{$nowTime}',                '{$nowTime}',                NULL,        NULL             );";

        $sql1 .= "\n";

        $sql1 .= "INSERT INTO `t_externaluserhis` (            `BrandID`,            `ExternalUserInfoID`,            `QyUserID`,            `StoreUserHisID`,            `Description`,            `Remark`,            `RemarkCorpName`,            `RemarkMobiles`,            `State`,            `TagIds`,            `DeleteTime`,            `DeleteWay`,            `DeleteBy`,            `OperUserid`,            `AddWay`,            `TransferID`,            `TransferState`,            `FromAssign`,            `DelByTransfer`,            `StoreMarkUserId`,            `StoreMarkUserHisId`,            `CreateTimeSpan`,            `AddTime`,            `CreateTime`,            `CreateBy`,            `UpdateTime`,            `UpdateBy`,            `StoreMarkUserIdBak`,            `org_code`         )        VALUES            (                1,                {$infoId},                '13918935904',                1419,                '',                '{$name}',                NULL,                '[]',                NULL,                '',                NULL,                NULL,                NULL,                '13918935904',                1,                '17150eef6bca471386f3ee91a688494320221025',                NULL,                 '0',                 '0',                'f57999d152b2f2cea5da52903f8315cd',                1419,                '1666675808',                '{$nowTime}',                '{$nowTime}',                '外部联系人事件',                NULL,                NULL,                NULL,            'retail'             );";

        $sql1 .= "\n";

        return $sql1;
    }
}
