<?php

namespace vladayson\AccessRules\models;

use app\models\Users;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;

/**
 * This is the model class for table "roles".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $parent_id
 *
 * @property Roles $parent
 * @property Roles[] $roles
 * @property RolesPermissions[] $rolesPermissions
 * @property RolesUsers[] $rolesUsers
 * @property Permissions[] $permissions
 */
class Roles extends BaseModel
{
    public function behaviors()
    {
        return [
            'saveRelations' => [
                'class'     => SaveRelationsBehavior::class,
                'relations' => [
                    'permissions',
                    'roles'
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'roles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Roles::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'parent_id' => 'Parent ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Roles::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Roles::class, ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRolesPermissions()
    {
        return $this->hasMany(RolesPermissions::class, ['role_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRolesUsers()
    {
        return $this->hasMany(RolesUsers::class, ['role_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::class, ['user_id' => 'id'])->via('rolesUsers');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPermissions()
    {
        return $this->hasMany(Permissions::class, ['id' => 'permission_id'])->via('rolesPermissions');
    }

    /**
     * {@inheritdoc}
     * @return RolesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RolesQuery(get_called_class());
    }

    /**
     * @param $userId
     *
     * @return array|Roles[]
     */
    public static function getUserAssignments($userId)
    {
        return self::find()
            ->joinWith('users')
            ->joinWith('permissions')
            ->andWhere([Users::tableName() . '.id' => $userId])
            ->select(Permissions::tableName() . '.*')
            ->all();
    }

    /**
     * @param $roleName
     *
     * @return array
     */
    public static function getUsersByRole($roleName)
    {
        return self::find()
            ->select(RolesUsers::tableName() . '.user_id')
            ->innerJoin(
                RolesUsers::tableName(),
                RolesUsers::tableName() . '.role_id = ' . Roles::tableName() . '.id OR ' .
                RolesUsers::tableName() . '.role_id = ' . Roles::tableName() . '.parent_id'
            )
            ->andWhere([self::tableName() . '.name' => $roleName])
            ->column();
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    public static function getUserRole(int $userId)
    {
        return Roles::find()
            ->alias('r')
            ->select(['r.name'])
            ->innerJoin(RolesUsers::tableName() . ' AS ru', 'ru.role_id = r.id OR ru.role_id = r.parent_id')
            ->andWhere(['ru.user_id' => $userId])
            ->scalar();
    }
}
