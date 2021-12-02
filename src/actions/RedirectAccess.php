<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiShortUrl\actions;


use Yii;
use yii\base\Action;
use YiiHelper\helpers\Req;
use YiiShortUrl\models\ShortUrlFlag;
use Zf\Helper\Business\IpHelper;
use Zf\Helper\Exceptions\CustomException;
use Zf\Helper\Format;

/**
 * 操作 : 提供给前端供短链转发
 *
 * Class RedirectAccess
 * @package YiiShortUrl\actions
 */
class RedirectAccess extends Action
{
    /**
     * 运行逻辑
     *
     * @throws CustomException
     * @throws \yii\base\ExitException
     */
    public function run()
    {
        $code  = Yii::$app->getRequest()->getQueryParam('code');
        $short = $this->getShort($code);

        // ip有效性检查
        if ($short->expire_ip && !IpHelper::inRanges(Req::getUserIp(), explode_data($short->expire_ip, '|'))) {
            throw new CustomException('无权限访问链接');
        }
        // 时间有效性
        if ($short->type == ShortUrlFlag::TYPE_TEMPORARY) {
            $today = Format::date();
            // 用户生效判断
            if ($short->expire_begin_date > '1900-01-01' && $short->expire_begin_date > $today) {
                throw new CustomException('无效访问链接');
            }
            // 用户失效判断
            if ($short->expire_end_date > '1900-01-01' && $short->expire_end_date < $today) {
                throw new CustomException('无效访问链接');
            }
        }
        // 保存访问信息
        $short->times     = $short->times + 1;
        $short->access_at = Format::datetime();
        $short->save();
        // 页面重定向
        $this->controller->redirect($short->source->url);
        Yii::$app->end();
    }

    /**
     * 返回 ShortUrlFlag 模型
     *
     * @param string $code
     * @return ShortUrlFlag|null
     * @throws CustomException
     */
    protected function getShort($code): ShortUrlFlag
    {
        $short = ShortUrlFlag::findOne([
            'flag' => $code
        ]);
        if (null === $short || $short->is_deleted) {
            throw new CustomException('不存在的链接访问');
        }
        return $short;
    }
}