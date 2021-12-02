<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiShortUrl\logics;


use YiiHelper\helpers\Pager;
use YiiHelper\traits\TQueryWhere;
use YiiShortUrl\models\ShortUrlFlag;
use YiiShortUrl\models\ShortUrlSource;
use Zf\Helper\Abstracts\Factory;
use Zf\Helper\Exceptions\CustomException;
use Zf\Helper\Format;
use Zf\Helper\Traits\Models\TLabelCompareRelation;
use Zf\Helper\Traits\Models\TLabelOrderBy;

/**
 * 逻辑 : 短链系统逻辑处理
 *
 * Class ShortUrl
 * @package YiiShortUrl\logics
 */
class LogicShortUrl extends Factory
{
    use TQueryWhere;

    /**
     * URL短链列表
     *
     * @param array $params
     * @return array
     * @throws \Zf\Helper\Exceptions\ProgramException
     */
    public function list(array $params)
    {
        $query = ShortUrlFlag::find()
            ->alias('flag')
            ->joinWith('source');
        // 等于查询
        $this->attributeWhere($query, $params, [
            'flag.md5'        => 'md5',
            'flag.type'       => 'type',
            'flag.is_deleted' => 'is_deleted',
        ]);
        // 时间查询
        $query->andFilterWhere(['>=', 'flag.created_at', $params['create_start_at'] ?? null]);
        $query->andFilterWhere(['<=', 'flag.created_at', $params['create_end_at'] ?? null]);
        $query->andFilterWhere(['>=', 'flag.access_at', $params['access_start_at'] ?? null]);
        $query->andFilterWhere(['<=', 'flag.access_at', $params['access_end_at'] ?? null]);
        if (isset($params['times']) && $params['times'] > 0) {
            $compareRelation = TLabelCompareRelation::getCompareEntity($params['compareRelation'] ?? COMPARE_GT);
            $query->andFilterWhere([$compareRelation, 'flag.times', $params['times']]);
        }
        if (isset($params['orderByField']) && !empty($params['orderByField'])) {
            $orderWay = TLabelOrderBy::getOrderByEntity($params['orderByWay'] ?? ORDER_DESC);
            $query->orderBy("flag.{$params['orderByField']} {$orderWay}");
        }
        // like 查询
        if (isset($params['url']) && !empty($params['url'])) {
            $query->andFilterWhere(['LIKE', '`source`.`url`', "{$params['url']}"]);
        }

        // 是否有效查询
        if (isset($params['isExpire']) && "" !== $params['isExpire'] && null !== $params['isExpire']) {
            $nowDatetime = Format::datetime();
            if ($params['isExpire']) {
                // 有效用户
                $query->andWhere([
                    'or',
                    [ // 未设置有效期的
                      'and',
                      ['<', 'flag.expire_end_date', EMPTY_TIME_MIN],
                      ['<', 'flag.expire_end_date', EMPTY_TIME_MIN],
                    ], [
                        'not', // 有效反转
                        [ // 有效期的
                          'and',
                          ['<', 'flag.expire_end_date', $nowDatetime],
                          ['>', 'flag.expire_begin_date', $nowDatetime],
                        ]
                    ]
                ]);
            } else {
                // 失效用户
                $query->andWhere([
                    'and',
                    ['<', 'flag.expire_end_date', $nowDatetime],
                    ['>', 'flag.expire_begin_date', $nowDatetime],
                ]);
            }
        }
        return Pager::getInstance()->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 添加资源信息
     *
     * @param array $params
     * @return bool
     * @throws CustomException
     * @throws \yii\db\Exception
     */
    public function add(array $params)
    {
        // 一些常规检查检查
        if (!isset($params['url'])) {
            throw new CustomException("构建短链必须设置url");
        }
        if (!is_url($params['url'])) {
            throw new CustomException("请正确填写url链接");
        }
        // 如果存在就不创建
        $md5   = $this->getFlagMd5($params);
        $model = ShortUrlFlag::findOne([
            'md5' => $md5,
        ]);
        if (null !== $model) {
            return true;
        }
        // 获取url资源
        $source = $this->getSavedSource($params);
        $model  = new ShortUrlFlag();
        $model->setAttributes([
            'url_source_id' => $source->id,
            'md5'           => $md5,
            'desc'          => $params['desc'] ?? '',
            'expire_ip'     => $params['expire_ip'] ?? '',
        ]);
        $model->type = ShortUrlFlag::TYPE_PERMANENT;
        if (isset($params['expire_begin_date']) && !empty($params['expire_begin_date'])) {
            $model->type              = ShortUrlFlag::TYPE_TEMPORARY;
            $model->expire_begin_date = $params['expire_begin_date'];
        }
        if (isset($params['expire_end_date']) && !empty($params['expire_end_date'])) {
            $model->type            = ShortUrlFlag::TYPE_TEMPORARY;
            $model->expire_end_date = $params['expire_end_date'];
        }
        return $model->saveOrException();
    }

    /**
     * 编辑URL短链
     *
     * @param array $params
     * @return bool
     * @throws CustomException
     * @throws \yii\db\Exception
     */
    public function edit(array $params)
    {
        $model = $this->getShortFlagModel($params);
        $model->setAttributes([
            'desc'       => $params['desc'] ?? '',
            'expire_ip'  => $params['expire_ip'] ?? '',
            'updated_at' => Format::datetime(),
            'is_deleted' => IS_DELETED_NO
        ]);
        $model->type = ShortUrlFlag::TYPE_PERMANENT;
        if (isset($params['expire_begin_date']) && !empty($params['expire_begin_date'])) {
            $model->type              = ShortUrlFlag::TYPE_TEMPORARY;
            $model->expire_begin_date = $params['expire_begin_date'];
        }
        if (isset($params['expire_end_date']) && !empty($params['expire_end_date'])) {
            $model->type            = ShortUrlFlag::TYPE_TEMPORARY;
            $model->expire_end_date = $params['expire_end_date'];
        }
        $model->md5 = $this->getFlagMd5($model);
        return $model->saveOrException();
    }

    /**
     * 删除URL短链
     *
     * @param array $params
     * @return bool
     * @throws CustomException
     * @throws \yii\db\Exception
     */
    public function del(array $params)
    {
        $model = $this->getShortFlagModel($params);
        if ($model->is_deleted == IS_DELETED_YES) {
            throw new CustomException('短链标识已删除');
        }
        $model->is_deleted = IS_DELETED_YES;
        $model->updated_at = Format::datetime();
        return $model->saveOrException();
    }

    /**
     * 查看URL短链详情
     *
     * @param array $params
     * @return ShortUrlFlag
     * @throws CustomException
     */
    public function view(array $params)
    {
        return $this->getShortFlagModel($params);
    }

    /**
     * 获取操作的短链标识模型
     *
     * @param array $params
     * @return ShortUrlFlag
     * @throws CustomException
     */
    protected function getShortFlagModel(array $params): ShortUrlFlag
    {
        $model = ShortUrlFlag::findOne([
            'id' => $params['id'],
        ]);
        if (null === $model) {
            throw new CustomException("短链标记不存在");
        }
        return $model;
    }

    /**
     * 获取url资源库，没有则创建
     *
     * @param array $params
     * @return ShortUrlSource
     * @throws \yii\db\Exception
     */
    protected function getSavedSource(array $params): ShortUrlSource
    {
        $md5 = md5($params['url']);
        // 查找资源库中是否存在
        $source = ShortUrlSource::findOne([
            'md5' => $md5,
        ]);
        if (null === $source) {
            $source = new ShortUrlSource();
            $source->setAttributes([
                'url' => $params['url'],
                'md5' => $md5,
            ]);
            $source->saveOrException();
        }
        return $source;
    }

    /**
     * 获取url短链标记记录md5值
     *
     * @param array|ShortUrlFlag $params
     * @return string
     * @throws CustomException
     */
    protected function getFlagMd5($params): string
    {
        if (is_array($params)) {
            $md5Array = [
                'url'               => $params['url'] ?? '',
                'expire_ip'         => $params['expire_ip'] ?? '',
                'expire_begin_date' => $params['expire_begin_date'] ?? '',
                'expire_end_date'   => $params['expire_end_date'] ?? '',
            ];
        } else if ($params instanceof ShortUrlFlag) {
            $md5Array = [
                'url'               => $params->source->url,
                'expire_ip'         => $params->expire_ip,
                'expire_begin_date' => $params->expire_begin_date,
                'expire_end_date'   => $params->expire_end_date,
            ];
        } else {
            throw new CustomException('获取flag参数不正确');
        }
        return md5(json_encode($md5Array));
    }
}