<?php
/**
 * Template name: 状态监控页
 * Description: 这是状态监控页的模板
 */

use AlibabaCloud\SDK\Cms\V20190101\Cms;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Cms\V20190101\Models\DescribeSiteMonitorStatisticsRequest;

class Sample {

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Cms Client
     */
    public static function createClient($accessKeyId, $accessKeySecret){
        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 您的AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "metrics.cn-hangzhou.aliyuncs.com";
        return new Cms($config);
    }

    /**
     * @param string[] $args
     * @return void
     */
    public static function main($args){
        $client = self::createClient("accessKeyId", "accessKeySecret");
        $describeSiteMonitorStatisticsRequest = new DescribeSiteMonitorStatisticsRequest([
            "taskId" => "zz"
        ]);
        // 复制代码运行请自行打印 API 的返回值
        $client->describeSiteMonitorStatistics($describeSiteMonitorStatisticsRequest);
    }
}
$path = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($path)) {
    require_once $path;
}
Sample::main(array_slice($argv, 1));
?>
