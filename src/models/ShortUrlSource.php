<?php

namespace YiiShortUrl\models;

use YiiHelper\abstracts\Model;

/**
 * This is the model class for table "short_url_source".
 *
 * @property int $id 自增ID
 * @property string $url 真实URL
 * @property string $md5 URL的md5码
 * @property string|null $desc 描述
 * @property string $created_at 创建时间
 */
class ShortUrlSource extends Model
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'short_url_source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url'], 'required'],
            [['created_at'], 'safe'],
            [['url'], 'string', 'max' => 255],
            [['md5'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => '自增ID',
            'url'        => '真实URL',
            'md5'        => 'URL的md5码',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 获取关联Url资源库
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasMany(ShortUrlFlag::class, [
            'url_source_id' => 'id',
        ]);
    }
}
