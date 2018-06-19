<?php
/**
 * 假如这是一个异步文件处理消费者命令入口文件
 *
 * Created by li hua.
 * User: m
 * Date: 2018/6/19
 * Time: 23:42
 */

namespace App\Console\Commands\Nsq;

use App\Console\Server\NsqServer;
use Illuminate\Console\Command;

class FileCmd extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nsq:file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'nsq 文件异步任务';

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
     * 执行
     */
    public function handle()
    {
        try {
            $server = new NsqServer('file');
            $server->start();
        } catch ( \Exception $e ) {
            exit($e);
        }
    }
}