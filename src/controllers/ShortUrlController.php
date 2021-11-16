<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiShortUrl\controllers;


use Exception;
use YiiHelper\abstracts\RestController;
use YiiShortUrl\models\ShortUrlFlag;
use YiiShortUrl\services\interfaces\IShortUrlService;
use YiiShortUrl\services\ShortUrlService;
use Zf\Helper\Traits\Models\TLabelCompareRelation;
use Zf\Helper\Traits\Models\TLabelDeleted;
use Zf\Helper\Traits\Models\TLabelOrderBy;
use Zf\Helper\Traits\Models\TLabelYesNo;

/**
 * 控制器 : URL短链后台管理
 *
 * Class ShortUrlController
 * @package YiiShortUrl\controllers
 *
 * @property-read IShortUrlService $service
 */
class ShortUrlController extends RestController
{
    public $serviceInterface = IShortUrlService::class;
    public $serviceClass     = ShortUrlService::class;

    /**
     * URL短链列表
     *
     * @return array
     * @throws Exception
     */
    public function actionList()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['url', 'string', 'label' => 'URL'],
            ['md5', 'string', 'label' => 'MD5', 'length' => 32],
            ['type', 'in', 'label' => '链接类型', 'range' => array_keys(ShortUrlFlag::types())],
            ['is_deleted', 'in', 'label' => '是否删除', 'range' => array_keys(TLabelDeleted::deletedLabels())],
            ['create_start_at', 'datetime', 'label' => '创建开始时间', 'format' => 'php:Y-m-d H:i:s'],
            ['create_end_at', 'datetime', 'label' => '创建结束时间', 'format' => 'php:Y-m-d H:i:s'],
            ['access_start_at', 'datetime', 'label' => '访问开始时间', 'format' => 'php:Y-m-d H:i:s'],
            ['access_end_at', 'datetime', 'label' => '访问结束时间', 'format' => 'php:Y-m-d H:i:s'],
            // 访问次数规则
            ['times', 'string', 'label' => '访问次数', 'length' => 32],
            ['compareRelation', 'in', 'label' => '对比关系', 'default' => COMPARE_GE, 'range' => array_keys(TLabelCompareRelation::compareLabels())],
            // 排序规则
            ['orderByField', 'in', 'label' => '排序字段', 'range' => array_keys(ShortUrlFlag::orderAbilityFields())],
            ['orderByWay', 'in', 'label' => '排序方式', 'default' => ORDER_DESC, 'range' => array_keys(TLabelOrderBy::orderByLabels())],
            // 有效期规则
            ['isExpire', 'in', 'label' => '是否有效', 'range' => array_keys(TLabelYesNo::isLabels())],
        ], null, true);
        // 业务处理
        $res = $this->service->list($params);
        // 渲染结果
        return $this->success($res, '短链列表');
    }

    /**
     * 添加URL短链
     *
     * @return array
     * @throws Exception
     */
    public function actionAdd()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['url'], 'required'],
            ['url', 'string', 'label' => 'URL'],
            ['expire_ip', 'ip', 'label' => '有效IP地址'],
            ['expire_begin_at', 'datetime', 'label' => '生效日期', 'format' => 'php:Y-m-d'],
            ['expire_end_at', 'datetime', 'label' => '失效日期', 'format' => 'php:Y-m-d'],
            ['desc', 'string', 'label' => '描述', 'max' => 255],
        ]);
        // 业务处理
        $res = $this->service->add($params);
        // 渲染结果
        return $this->success($res, '添加短链成功');
    }

    /**
     * 编辑URL短链
     *
     * @return array
     * @throws Exception
     */
    public function actionEdit()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id', 'desc'], 'required'],
            ['id', 'exist', 'label' => '短链标记ID', 'targetClass' => ShortUrlFlag::class, 'targetAttribute' => 'id'],
            ['desc', 'string', 'label' => '描述', 'max' => 255],
        ]);
        // 业务处理
        $res = $this->service->edit($params);
        // 渲染结果
        return $this->success($res, '编辑短链成功');
    }

    /**
     * 删除URL短链
     *
     * @return array
     * @throws Exception
     */
    public function actionDel()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id'], 'required'],
            ['id', 'exist', 'label' => '短链标记ID', 'targetClass' => ShortUrlFlag::class, 'targetAttribute' => 'id'],
        ]);
        // 业务处理
        $res = $this->service->del($params);
        // 渲染结果
        return $this->success($res, '删除短链成功');
    }

    /**
     * 查看URL短链详情
     *
     * @return array
     * @throws Exception
     */
    public function actionView()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id'], 'required'],
            ['id', 'exist', 'label' => '短链标记ID', 'targetClass' => ShortUrlFlag::class, 'targetAttribute' => 'id'],
        ]);
        // 业务处理
        $res = $this->service->view($params);
        // 渲染结果
        return $this->success($res, '查看短链信息');
    }
}