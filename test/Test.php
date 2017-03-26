<?php



class Writer
{
    /**
     * "Magic" identifier - for version we support
     */
    const MAGIC_V2 = "  V2";

    /**
     * Magic hello
     *
     * @return string
     */
    public function magic()
    {
        return self::MAGIC_V2;
    }

    /**
     * Subscribe [SUB]
     *
     * @param string $topic
     * @param string $channel
     * @param string $shortId
     * @param string $longId
     *
     * @return string
     */
    public function subscribe($topic, $channel, $shortId, $longId)
    {
        return $this->command('SUB', $topic, $channel, $shortId, $longId);
    }

    /**
     * Publish [PUB]
     *
     * @param string $topic
     * @param string $message
     *
     * @return string
     */
    public function publish($topic, $message)
    {
        // the fast pack way, but may be unsafe
        $cmd = $this->command('PUB', $topic);
        $size = pack('N', strlen($message));
        return $cmd . $size . $message;

        // the safe way, but is time cost
        // $cmd = $this->command('PUB', $topic);
        // $data = $this->packString($message);
        // $size = pack('N', strlen($data));
        // return $cmd . $size . $data;
    }

    /**
     * Ready [RDY]
     *
     * @param integer $count
     *
     * @return string
     */
    public function ready($count)
    {
        return $this->command('RDY', $count);
    }

    /**
     * Finish [FIN]
     *
     * @param string $id
     *
     * @return string
     */
    public function finish($id)
    {
        return $this->command('FIN', $id);
    }

    /**
     * Requeue [REQ]
     *
     * @param string $id
     * @param integer $timeMs
     *
     * @return string
     */
    public function requeue($id, $timeMs)
    {
        return $this->command('REQ', $id, $timeMs);
    }

    /**
     * No-op [NOP]
     *
     * @return string
     */
    public function nop()
    {
        return $this->command('NOP');
    }

    /**
     * Cleanly close [CLS]
     *
     * @return string
     */
    public function close()
    {
        return $this->command('CLS');
    }

    /**
     * Command
     *
     * @return string
     */
    private function command()
    {
        $args = func_get_args();
        $cmd = array_shift($args);
        return sprintf("%s %s%s", $cmd, implode(' ', $args), "\n");
    }

    /**
     * Pack string -> binary
     *
     * @param string $str
     *
     * @return string Binary packed
     */
    private function packString($str)
    {
        $outStr = "";
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $outStr .= pack("c", ord(substr($str, $i, 1)));
        }
        return $outStr;
    }
}

/**
 *
 * Date: 17-3-25
 * Time: 下午5:20
 * author :李华 yehong0000@163.com
 */
class Test
{
    public static function init()
    {
        $nsqd = self::lookupHosts();
        $serv = new Swoole\Server("127.0.0.1", 9502);
        $serv->set(array('task_worker_num' => 4));
        $serv->on('Receive', function($serv, $fd, $from_id, $data) {
            $task_id = $serv->task("Async");
            echo "Dispath AsyncTask: id=$task_id\n";
        });
        $serv->on('Task', function ($serv, $task_id, $from_id, $data) {
            echo "New AsyncTask[id=$task_id]".PHP_EOL;
            $serv->finish("$data -> OK");
        });
        $serv->on('Finish', function ($serv, $task_id, $data) {
            echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
        });
        $serv->on('WorkerStart', function ($serv, $worker_id){
            global $argv;
            if($worker_id >= $serv->setting['worker_num']) {
                echo 'task start'."\n";
            } else {
                $serv->task('xxxx');
            }
        });
        $serv->start();
        return;
//        $clitnts=[];
        foreach ($nsqd as $host) {
            $parts = explode(':', $host);

            $clitnts=self::setClient($parts[0],$parts[1]);
        }
    }

    public static function setClient($host,$port)
    {
        $Writer=new Writer();
        $hn = gethostname();
        $parts = explode('.', $hn);
        $shortId = $parts[0];
        $longId = $hn;
        $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->on("connect", function (swoole_client $cli) use($Writer ,$shortId,$longId){
            $cmd=$Writer->subscribe('nsq_common', 'web_member', $shortId, $longId);
            echo $cmd,"\n";
            $cli->send($Writer->magic());
            $cli->send($cmd);
            $cli->send($Writer->ready(1));
        });
        $client->on("receive", function (swoole_client $cli, $data) {
            echo "Receive: $data\n";
            $cli->send(str_repeat('A', 100) . "\n");
            sleep(1);
        });
        $client->on("error", function (swoole_client $cli) {
            echo "error\n";
        });
        $client->on("close", function (swoole_client $cli) {
            echo "Connection close\n";
        });
        swoole_async_dns_lookup($host, function($host, $ip) use($client,$port){
            $client->connect($ip, $port);
        });
    }

    public static function lookupHosts($hosts = ['127.0.0.1:4161'], $topic = 'nsq_common')
    {
        $lookupHosts = array();

        foreach ($hosts as $host) {
            // ensure host; otherwise go with default (:4161)
            if (strpos($host, ':') === FALSE) {
                $host .= ':4161';
            }

            $url = "http://{$host}/lookup?topic=" . urlencode($topic);
            $ch = curl_init($url);
            $options = array(
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HEADER         => FALSE,
                CURLOPT_FOLLOWLOCATION => FALSE,
                CURLOPT_ENCODING       => '',
                CURLOPT_USERAGENT      => 'lib\framework\nsq',
                CURLOPT_CONNECTTIMEOUT => 50,
                CURLOPT_TIMEOUT        => 50,
                CURLOPT_FAILONERROR    => TRUE
            );
            curl_setopt_array($ch, $options);
            $r = curl_exec($ch);
            $r = json_decode($r, TRUE);

            // don't fail since we can't distinguish between bad topic and general failure
            /*
            if (!is_array($r)) {
                throw new LookupException(
                        "Error talking to nsqlookupd via $url"
                        );
            }*/

            $producers = isset($r['data'], $r['data']['producers']) ? $r['data']['producers'] : array();
            foreach ($producers as $prod) {
                if (isset($prod['address'])) {
                    $address = $prod['address'];
                } else {
                    $address = $prod['broadcast_address'];
                }
                $h = "{$address}:{$prod['tcp_port']}";
                if (!in_array($h, $lookupHosts)) {
                    $lookupHosts[] = $h;
                }

            }
        }

        return $lookupHosts;
    }
}

Test::init();