<?php

namespace YiiShortUrl\models;

use Yii;
use yii\db\ActiveRecord;
use YiiHelper\abstracts\Model;
use YiiHelper\behaviors\DefaultBehavior;
use Zf\Helper\NumericTransform;

/**
 * This is the model class for table "short_url_flag".
 *
 * @property int $id 自增ID
 * @property int $url_source_id url资源ID
 * @property string $md5 URL的md5码
 * @property string $type 类型[permanent:永久,temporary:临时]
 * @property string|null $desc 描述
 * @property string $flag 短链标记
 * @property int $is_deleted 是否删除
 * @property int $times 链接调用次数
 * @property string $expire_ip 有效IP地址
 * @property string $expire_begin_date 生效日期
 * @property string $expire_end_date 失效日期
 * @property string $created_at 创建时间
 * @property string $access_at 最后访问时间
 * @property string $updated_at 更新时间
 *
 * @property-read ShortUrlSource $source
 * @property-read string $shortUrl
 */
class ShortUrlFlag extends Model
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'short_url_flag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url_source_id', 'type'], 'required'],
            [['url_source_id', 'is_deleted', 'times'], 'integer'],
            [['expire_begin_date', 'expire_end_date', 'created_at', 'access_at', 'updated_at'], 'safe'],
            [['md5'], 'string', 'max' => 32],
            [['type'], 'string', 'max' => 20],
            [['desc', 'expire_ip'], 'string', 'max' => 255],
            [['flag'], 'string', 'max' => 6],
            [['flag'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => '自增ID',
            'url_source_id'     => 'url资源ID',
            'md5'               => 'URL的md5码',
            'type'              => '类型[permanent:永久,temporary:临时]',
            'desc'              => '描述',
            'flag'              => '短链标记',
            'is_deleted'        => '是否删除',
            'times'             => '链接调用次数',
            'expire_ip'         => '有效IP地址',
            'expire_begin_date' => '生效日期',
            'expire_end_date'   => '失效日期',
            'created_at'        => '创建时间',
            'access_at'         => '最后访问时间',
            'updated_at'        => '更新时间',
        ];
    }

    /**
     * 绑定 behavior
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'      => DefaultBehavior::class,
                'type'       => DefaultBehavior::TYPE_DATETIME,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['expire_begin_date', 'expire_end_date'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['expire_begin_date', 'expire_end_date'],
                ],
            ],
        ];
    }

    /**
     * 数据插入成功后，计算 url-flag 并保存入库
     *
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \Zf\Helper\Exceptions\ProgramException
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->flag = NumericTransform::decToBase($this->id, 62, 6);
            $this->save();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * 构建访问短链接
     *
     * @return string
     */
    public function getShortUrl()
    {
        $baseUrlForShortUrl = rtrim(Yii::$app->params['baseUrlForShortUrl'] ?? (Yii::$app->getRequest()->getHostInfo() . '/s'), '/');
        return "{$baseUrlForShortUrl}/{$this->flag}";
    }

    /**
     * json 导出字段
     *
     * @return array|false
     */
    public function fields()
    {
        $fields             = parent::fields();
        $fields['source']   = 'source';
        $fields['shortUrl'] = 'shortUrl';
        return $fields;
    }

    /**
     * 获取关联Url资源库
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(ShortUrlSource::class, [
            'id' => 'url_source_id',
        ])->alias('source');
    }

    const TYPE_PERMANENT = 'permanent';
    const TYPE_TEMPORARY = 'temporary';

    /**
     * 短链有效类型
     *
     * @return array
     */
    public static function types()
    {
        return [
            self::TYPE_PERMANENT => '永久有效', // permanent
            self::TYPE_TEMPORARY => '临时有效', // temporary
        ];
    }

    /**
     * 可排序的字段
     *
     * @return array
     */
    public static function orderAbilityFields()
    {
        return [
            'id'         => '自增ID',
            'times'      => '访问次数',
            'created_at' => '生成时间',
            'updated_at' => '最后访问时间',
        ];
    }
}
