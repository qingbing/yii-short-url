<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiShortUrl\services;


use yii\db\Exception;
use YiiHelper\abstracts\Service;
use YiiShortUrl\logics\LogicShortUrl;
use YiiShortUrl\services\interfaces\IShortUrlService;
use Zf\Helper\Exceptions\CustomException;

/**
 * 服务 : URL短链后台管理
 *
 * Class ShortUrlService
 * @package YiiShortUrl\services
 */
class ShortUrlService extends Service implements IShortUrlService
{
    /**
     * URL短链列表
     *
     * @param array|null $params
     * @return array
     * @throws \Zf\Helper\Exceptions\ProgramException
     */
    public function list(array $params = []): array
    {
        return LogicShortUrl::getInstance()
            ->list($params);
    }

    /**
     * 添加URL短链
     *
     * @param array $params
     * @return bool
     * @throws CustomException
     * @throws Exception
     */
    public function add(array $params): bool
    {
        return LogicShortUrl::getInstance()
            ->add($params);
    }

    /**
     * 编辑URL短链
     *
     * @param array $params
     * @return bool
     * @throws CustomException
     * @throws Exception
     */
    public function edit(array $params): bool
    {
        return LogicShortUrl::getInstance()
            ->edit($params);
    }

    /**
     * 删除URL短链
     *
     * @param array $params
     * @return bool
     * @throws CustomException
     * @throws Exception
     */
    public function del(array $params): bool
    {
        return LogicShortUrl::getInstance()
            ->del($params);
    }

    /**
     * 查看URL短链详情
     *
     * @param array $params
     * @return mixed
     * @throws CustomException
     */
    public function view(array $params)
    {
        return LogicShortUrl::getInstance()
            ->view($params);
    }
}